<?php
declare(strict_types=1);

// Kullanım: php scripts/migrate.php  — 9 tabloyu oluşturur, başlangıç hiyerarşisini ekler.
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

$pdo = Database::pdo();

$pdo->exec("CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS sectors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS sub_sectors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sector_id INTEGER NOT NULL REFERENCES sectors(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    keywords TEXT NOT NULL DEFAULT '',
    is_fallback INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS companies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sub_sector_id INTEGER NOT NULL REFERENCES sub_sectors(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    note TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

// Eski haber-toplayıcı modeli kaldırıldı (yeniden tasarım — bkz. plan). Problem
// keşif modeline geçildi: aynı problem tek "problems" kaydında kümelenir, ham
// kanıtlar "problem_mentions"da tutulur. Kullanıcı onayıyla eski signals sıfırlanır.
$pdo->exec("DROP TABLE IF EXISTS signals");

// problems = kümelenmiş problem/fırsat kaydı (50 kişi → tek kayıt). AI analizi
// (12 boyut) ve fırsat metinleri bu satırda tutulur.
$pdo->exec("CREATE TABLE IF NOT EXISTS problems (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sub_sector_id INTEGER REFERENCES sub_sectors(id) ON DELETE SET NULL,
    region TEXT NOT NULL DEFAULT 'TR',
    cluster_key TEXT NOT NULL DEFAULT '',
    title TEXT NOT NULL,
    content_tr TEXT,
    why_summary TEXT NOT NULL DEFAULT '',
    mvp_suggestion TEXT NOT NULL DEFAULT '',
    mvp_weeks INTEGER,
    mention_count INTEGER NOT NULL DEFAULT 1,
    source_variety INTEGER NOT NULL DEFAULT 1,
    sources TEXT NOT NULL DEFAULT '',
    -- 12 AI boyutu: 0-100 puanlar + metin alanları (revenue/frequency/trend)
    demand_score INTEGER,
    competition_score INTEGER,
    ai_solvability INTEGER,
    automation_fit INTEGER,
    technical_difficulty INTEGER,
    revenue_potential TEXT NOT NULL DEFAULT '',
    subscription_fit INTEGER,
    local_opportunity INTEGER,
    global_opportunity INTEGER,
    repeat_frequency TEXT NOT NULL DEFAULT '',
    trend_direction TEXT NOT NULL DEFAULT '',
    manual_score INTEGER NOT NULL DEFAULT 0,
    total_score INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'ham',
    is_favorite INTEGER NOT NULL DEFAULT 0,
    first_seen TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    last_seen TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

// problem_mentions = tekil ham kanıt (hangi kaynak, hangi URL, ne zaman).
// content_hash UNIQUE: aynı içerik tekrar taranırsa mention sayısı şişmez.
$pdo->exec("CREATE TABLE IF NOT EXISTS problem_mentions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    problem_id INTEGER NOT NULL REFERENCES problems(id) ON DELETE CASCADE,
    source TEXT NOT NULL DEFAULT 'manuel',
    source_url TEXT NOT NULL DEFAULT '',
    content TEXT NOT NULL,
    content_tr TEXT,
    content_hash TEXT NOT NULL UNIQUE,
    region TEXT NOT NULL DEFAULT 'TR',
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

$pdo->exec("CREATE INDEX IF NOT EXISTS idx_problems_cluster ON problems(sub_sector_id, region, cluster_key)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_mentions_problem ON problem_mentions(problem_id)");

$pdo->exec("CREATE TABLE IF NOT EXISTS projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    sector_id INTEGER REFERENCES sectors(id) ON DELETE SET NULL,
    status TEXT NOT NULL DEFAULT 'yeni',
    current_phase TEXT NOT NULL DEFAULT 'Planlama',
    region TEXT NOT NULL DEFAULT 'TR',
    source_links TEXT NOT NULL DEFAULT '',
    notes TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    phase TEXT NOT NULL DEFAULT 'Genel',
    title TEXT NOT NULL,
    description TEXT NOT NULL DEFAULT '',
    status TEXT NOT NULL DEFAULT 'acik',
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS ticks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    task_id INTEGER NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type TEXT NOT NULL,
    message TEXT NOT NULL,
    ref_id INTEGER,
    created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
)");

// --- Seed: başlangıç hiyerarşisi (yalnızca boşsa) ---
$count = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
if ($count === 0) {
    $seed = [
        'Teknoloji' => [
            'Yapay Zeka' => [
                ['AI Araçları & Otomasyon', 'yapay zeka, ai, chatbot, bot, otomasyon, automation, gpt, llm, agent, asistan'],
            ],
            'Yazılım' => [
                ['Web & Mobil Uygulama', 'uygulama, app, web sitesi, website, mobil, yazılım, software, saas, platform'],
                ['E-ticaret Çözümleri', 'e-ticaret, ecommerce, satış, mağaza, shopify, pazaryeri, store, dropshipping'],
            ],
        ],
        'Hizmet' => [
            'Yerel Hizmetler' => [
                ['Randevu & Rezervasyon', 'randevu, rezervasyon, booking, appointment, salon, klinik'],
                ['Lojistik & Kurye', 'kargo, kurye, teslimat, delivery, shipping, lojistik'],
            ],
        ],
        'Eğitim' => [
            'Online Eğitim' => [
                ['Kurs & İçerik Platformları', 'kurs, eğitim, course, öğren, learn, tutorial, ders'],
            ],
        ],
        'Finans' => [
            'Fintech' => [
                ['Ödeme & Finans Araçları', 'ödeme, payment, fatura, invoice, muhasebe, accounting, bütçe, budget, abonelik'],
            ],
        ],
    ];

    $insCat = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
    $insSec = $pdo->prepare('INSERT INTO sectors (category_id, name) VALUES (?, ?)');
    $insSub = $pdo->prepare('INSERT INTO sub_sectors (sector_id, name, keywords, is_fallback) VALUES (?, ?, ?, 0)');

    foreach ($seed as $catName => $sectorsArr) {
        $insCat->execute([$catName]);
        $catId = (int) $pdo->lastInsertId();
        foreach ($sectorsArr as $secName => $subs) {
            $insSec->execute([$catId, $secName]);
            $secId = (int) $pdo->lastInsertId();
            foreach ($subs as [$subName, $keywords]) {
                $insSub->execute([$secId, $subName, $keywords]);
            }
        }
    }

    // Fallback: sınıflandırılamayan sinyaller buraya düşer
    $insCat->execute(['Genel']);
    $catId = (int) $pdo->lastInsertId();
    $insSec->execute([$catId, 'Genel']);
    $secId = (int) $pdo->lastInsertId();
    $pdo->prepare('INSERT INTO sub_sectors (sector_id, name, keywords, is_fallback) VALUES (?, ?, ?, 1)')
        ->execute([$secId, 'Sınıflandırılmamış', '']);

    echo "Tablolar olusturuldu, baslangic hiyerarsisi eklendi.\n";
} else {
    echo "Tablolar hazir (seed atlandi, kategoriler mevcut).\n";
}
