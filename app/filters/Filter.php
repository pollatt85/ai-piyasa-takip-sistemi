<?php
declare(strict_types=1);

/**
 * Kalite filtresi arayüzü (kayıttan ÖNCE eleme). passes() false dönerse item
 * DB'ye hiç yazılmaz. Yeni filtre = bu arayüzü uygulayan sınıf + FilterPipeline listesine ekle.
 */
interface Filter
{
    /** @param array{title: string, summary: string} $item */
    public function passes(array $item, array $feed): bool;
}
