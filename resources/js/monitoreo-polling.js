document.addEventListener('DOMContentLoaded', () => {
    let fragment = document.querySelector('[data-monitoring-poll-url]');

    if (!(fragment instanceof HTMLElement)) {
        return;
    }

    const url = fragment.dataset.monitoringPollUrl;
    const interval = Number(fragment.dataset.monitoringPollInterval);

    if (!url || !Number.isFinite(interval) || interval <= 0) {
        console.error('No se pudo iniciar la actualización automática del monitoreo: configuración inválida.');
        return;
    }

    let requestPending = false;

    const refreshFragment = async () => {
        if (document.hidden || requestPending) {
            return;
        }

        requestPending = true;

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`La consulta de monitoreo respondió con estado ${response.status}.`);
            }

            const html = await response.text();
            const documentFragment = new DOMParser().parseFromString(html, 'text/html');
            const refreshedFragment = documentFragment.getElementById(fragment.id);

            if (!(refreshedFragment instanceof HTMLElement)) {
                throw new Error('La respuesta no contiene el fragmento de monitoreo esperado.');
            }

            if (fragment.classList.contains('show')) {
                refreshedFragment.classList.add('show');
            }

            fragment.replaceWith(refreshedFragment);
            fragment = refreshedFragment;
        } catch (error) {
            console.error('No se pudo actualizar el monitoreo; se mantienen los datos visibles.', error);
        } finally {
            requestPending = false;
        }
    };

    window.setInterval(refreshFragment, interval);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refreshFragment();
        }
    });
});
