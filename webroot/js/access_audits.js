(() => {
    const toggle = document.querySelector('[data-audit-cleanup-toggle]');
    const panel = document.getElementById('access-audits-cleanup');

    if (!(toggle instanceof HTMLButtonElement) || !(panel instanceof HTMLElement)) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        const shouldExpand = !isExpanded;

        panel.hidden = !shouldExpand;
        toggle.setAttribute('aria-expanded', String(shouldExpand));
        toggle.textContent = shouldExpand
            ? 'Ocultar limpieza de auditoría'
            : 'Mostrar limpieza de auditoría';
    });
})();