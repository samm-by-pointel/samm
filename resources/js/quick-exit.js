/**
 * Bouton « Quitter rapidement » - charte §12.2.
 * Vide les champs affichés puis remplace la page courante par une page neutre.
 * Ce bouton n'efface pas l'historique.
 */
import { router } from '@inertiajs/react';

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

export function leaveQuickly(url) {
    clearVisibleForms();
    // Rend illisibles les pages déjà visitées gardées dans l'historique chiffré.
    router.clearHistory();
    window.location.replace(url);
}

/**
 * Échap pressée deux fois de suite : même effet que le bouton.
 * Renvoie la fonction de nettoyage de l'écouteur.
 */
export function listenForDoubleEscape(url) {
    let lastEscapeAt = 0;

    const onKeyDown = (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        const now = Date.now();

        if (now - lastEscapeAt < DOUBLE_ESCAPE_DELAY_MS) {
            leaveQuickly(url);
        }

        lastEscapeAt = now;
    };

    document.addEventListener('keydown', onKeyDown);

    return () => document.removeEventListener('keydown', onKeyDown);
}
