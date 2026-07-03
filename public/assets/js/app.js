// TR çeviri butonu — event delegation, MyMemory API sunucu tarafında çağrılır.
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-translate');
    if (!btn) return;
    e.preventDefault();

    const wrap = btn.closest('[data-signal-id]');
    const textEl = wrap ? wrap.querySelector('.js-signal-text') : null;
    if (!wrap || !textEl || btn.classList.contains('is-loading')) return;

    if (btn.classList.contains('is-translated')) {
        textEl.textContent = wrap.dataset.original;
        btn.textContent = 'TR';
        btn.classList.remove('is-translated');
        return;
    }

    if (wrap.dataset.translated) {
        textEl.textContent = wrap.dataset.translated;
        btn.textContent = 'EN';
        btn.classList.add('is-translated');
        return;
    }

    const id = wrap.dataset.signalId;
    const errEl = wrap.querySelector('.translate-error');
    if (errEl) errEl.remove();
    btn.classList.add('is-loading');

    fetch((window.APP_BASE_URL || '') + '/signals/' + id + '/translate', { method: 'POST' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.classList.remove('is-loading');
            if (data.ok) {
                wrap.dataset.translated = data.text;
                textEl.textContent = data.text;
                btn.textContent = 'EN';
                btn.classList.add('is-translated');
            } else {
                btn.insertAdjacentHTML('afterend', '<span class="translate-error">çeviri yapılamadı</span>');
            }
        })
        .catch(function () {
            btn.classList.remove('is-loading');
            btn.insertAdjacentHTML('afterend', '<span class="translate-error">çeviri yapılamadı</span>');
        });
});
