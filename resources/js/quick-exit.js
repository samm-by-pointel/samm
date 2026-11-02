/**
 * Bouton « Quitter rapidement » - charte §12.2.
 * Vide les champs affichés puis remplace la page courante par une page neutre.
 * Sans JavaScript, le lien fonctionne seul (href). Ce bouton n'efface pas l'historique.
 */
const DOUBLE_ESCAPE_DELAY_MS = 1500;

function clearVisibleForms() {
    document.querySelectorAll('form').forEach((form) => form.reset());
    document.querySelectorAll('input, textarea, select').forEach((field) => {
        if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = false;
        } else if (field.type !== 'hidden' && field.type !== 'submit') {
            field.value = '';
        }
    });
}

export function initQuickExit() {
    const trigger = document.querySelector('[data-quick-exit]');

    if (!trigger) {
        return;
    }

    const leave = () => {
        clearVisibleForms();
        window.location.replace(trigger.dataset.quickExitUrl);
    };

    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        leave();
    });

    let lastEscapeAt = 0;

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        const now = Date.now();

        if (now - lastEscapeAt < DOUBLE_ESCAPE_DELAY_MS) {
            leave();
        }

        lastEscapeAt = now;
    });
}
