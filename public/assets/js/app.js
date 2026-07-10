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

        const wrap = btn.closest('[data-problem-id]');
        if (!wrap) return;
        const id = wrap.dataset.problemId;

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

        fetch((window.APP_BASE_URL || '') + '/problems/' + id + '/translate', { method: 'POST' })
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

// Tarama ilerlemesi — form AJAX'a çevrilir, sunucudan akan NDJSON satırlarıyla
// buton altındaki % çubuğu güncellenir (feed başına bir ilerleme olayı).
(function () {
    const form = document.querySelector('.sidebar-scan');
    if (!form) return;
    const btn = form.querySelector('button');
    const box = form.querySelector('.scan-progress');
    const bar = form.querySelector('.scan-progress-bar');
    const text = form.querySelector('.scan-progress-text');
    const base = window.APP_BASE_URL || '';

    // Tarama sonrası yenilenen sayfada sonuç mesajını flash olarak göster.
    const doneMessage = sessionStorage.getItem('scanMessage');
    if (doneMessage) {
        sessionStorage.removeItem('scanMessage');
        const flash = document.createElement('div');
        flash.className = 'flash flash-success';
        flash.textContent = doneMessage;
        const main = document.querySelector('.main');
        if (main) main.insertBefore(flash, main.firstChild);
    }

    function setBar(pct, label) {
        bar.style.width = pct + '%';
        text.textContent = label;
    }

    function handleEvent(ev) {
        if (ev.type === 'progress' && ev.total) {
            const pct = Math.min(99, Math.max(2, Math.round((ev.current / ev.total) * 100)));
            setBar(pct, '%' + pct + (ev.source ? ' — ' + ev.source : ''));
        } else if (ev.type === 'done') {
            setBar(100, '%100 — tamamlandı');
            if (ev.message) sessionStorage.setItem('scanMessage', ev.message);
            setTimeout(function () { window.location.href = base + '/'; }, 800);
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (btn.disabled) return;
        btn.disabled = true;
        box.hidden = false;
        setBar(2, 'Başlatılıyor…');
        fetch(base + '/scan/run', { method: 'POST', headers: { 'X-Requested-With': 'fetch' } })
            .then(function (r) {
                if (!r.ok || !r.body) throw new Error('HTTP ' + r.status);
                const reader = r.body.getReader();
                const decoder = new TextDecoder();
                let buf = '';
                return (function pump() {
                    return reader.read().then(function (chunk) {
                        if (!chunk.done) buf += decoder.decode(chunk.value, { stream: true });
                        let nl;
                        while ((nl = buf.indexOf('\n')) !== -1) {
                            const line = buf.slice(0, nl).trim();
                            buf = buf.slice(nl + 1);
                            if (line) handleEvent(JSON.parse(line));
                        }
                        if (!chunk.done) return pump();
                    });
                })();
            })
            .catch(function () {
                setBar(100, 'Hata — tekrar deneyin');
                btn.disabled = false;
            });
    });
})();
