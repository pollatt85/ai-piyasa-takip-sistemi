// TR çeviri popover'ı — metin yerinde kalır, çeviri küçük bir pencerede gösterilir.
(function () {
    let pop = null;
    let popFor = null;

    function closePop() {
        if (pop) {
            pop.remove();
            pop = null;
            popFor = null;
        }
    }

    function showPop(btn, signalId) {
        closePop();
        pop = document.createElement('div');
        pop.className = 'translate-pop';
        pop.innerHTML = '<div class="tp-head">Türkçe çeviri <button type="button" class="tp-close" aria-label="Kapat">×</button></div>'
            + '<div class="tp-body">Çevriliyor…</div>';
        document.body.appendChild(pop);
        popFor = signalId;

        const r = btn.getBoundingClientRect();
        const viewWidth = window.innerWidth || document.documentElement.clientWidth || 360;
        const popWidth = Math.min(340, Math.max(240, viewWidth - 28));
        let left = r.left + window.scrollX;
        if (left + popWidth > window.scrollX + viewWidth - 14) {
            left = Math.max(14, window.scrollX + viewWidth - popWidth - 14);
        }
        pop.style.left = left + 'px';
        pop.style.top = (r.bottom + window.scrollY + 6) + 'px';
        pop.style.width = popWidth + 'px';
    }

    function setPopText(text, isError) {
        if (!pop) return;
        const body = pop.querySelector('.tp-body');
        body.textContent = text;
        body.classList.toggle('tp-error', !!isError);
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.tp-close')) {
            closePop();
            return;
        }
        const btn = e.target.closest('.btn-translate');
        if (!btn) {
            if (!e.target.closest('.translate-pop')) closePop();
            return;
        }
        e.preventDefault();

        const wrap = btn.closest('[data-signal-id]');
        if (!wrap) return;
        const id = wrap.dataset.signalId;

        // Aynı butona tekrar tıklama: aç/kapa
        if (pop && popFor === id) {
            closePop();
            return;
        }

        showPop(btn, id);

        if (wrap.dataset.translated) {
            setPopText(wrap.dataset.translated);
            return;
        }

        fetch((window.APP_BASE_URL || '') + '/signals/' + id + '/translate', { method: 'POST' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    wrap.dataset.translated = data.text;
                    if (popFor === id) setPopText(data.text);
                } else if (popFor === id) {
                    setPopText('Çeviri şu an yapılamadı.', true);
                }
            })
            .catch(function () {
                if (popFor === id) setPopText('Çeviri şu an yapılamadı.', true);
            });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePop();
    });
})();

// Tarama ilerlemesi — form AJAX'a çevrilir, buton altında feed bazlı % çubuğu gösterilir.
(function () {
    const form = document.querySelector('.sidebar-scan');
    if (!form) return;
    const btn = form.querySelector('button');
    const box = form.querySelector('.scan-progress');
    const bar = form.querySelector('.scan-progress-bar');
    const text = form.querySelector('.scan-progress-text');
    const base = window.APP_BASE_URL || '';
    let timer = null;

    function setBar(pct, label) {
        bar.style.width = pct + '%';
        text.textContent = label;
    }

    function poll() {
        fetch(base + '/scan/status')
            .then(function (r) { return r.json(); })
            .then(function (p) {
                if (!p || !p.running || !p.total) return;
                const pct = Math.min(99, Math.round((p.current / p.total) * 100));
                setBar(Math.max(pct, 2), '%' + pct + (p.source ? ' — ' + p.source : ''));
            })
            .catch(function () {});
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (btn.disabled) return;
        btn.disabled = true;
        box.hidden = false;
        setBar(2, 'Başlatılıyor…');
        timer = setInterval(poll, 1000);
        fetch(base + '/scan/run', { method: 'POST', headers: { 'X-Requested-With': 'fetch' } })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function () {
                clearInterval(timer);
                setBar(100, '%100 — tamamlandı');
                setTimeout(function () { window.location.href = base + '/'; }, 800);
            })
            .catch(function () {
                clearInterval(timer);
                setBar(100, 'Hata — tekrar deneyin');
                btn.disabled = false;
            });
    });
})();
