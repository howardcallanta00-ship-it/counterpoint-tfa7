document.querySelector('.menu-toggle')?.addEventListener('click', function () {
    const open = this.getAttribute('aria-expanded') !== 'true';
    this.setAttribute('aria-expanded', String(open));
    document.querySelector('#main-nav').classList.toggle('open', open);
});
document.querySelectorAll('[data-avatar]').forEach(image => {
    image.addEventListener('error', function () {
        if (this.src !== this.dataset.fallback) this.src = this.dataset.fallback;
    }, { once: true });
});
document.querySelector('[data-error-summary]')?.focus();
