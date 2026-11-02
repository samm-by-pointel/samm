import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import { initQuickExit } from './quick-exit';

/**
 * Thème : Bootstrap ne lit pas prefers-color-scheme seul. Les jetons suivent déjà
 * la préférence système en CSS ; ici on aligne data-bs-theme pour les composants Bootstrap.
 */
function syncTheme(mediaQuery) {
    document.documentElement.setAttribute('data-bs-theme', mediaQuery.matches ? 'dark' : 'light');
}

const darkScheme = window.matchMedia('(prefers-color-scheme: dark)');
syncTheme(darkScheme);
darkScheme.addEventListener('change', syncTheme);

initQuickExit();

/**
 * « Copier les codes » (charte §12.6) : copie le texte de la cible dans le presse-papiers
 * et le confirme en toutes lettres. Sans presse-papiers disponible, le texte reste sélectionnable.
 */
document.querySelectorAll('[data-copy-target]').forEach((button) => {
    button.addEventListener('click', async () => {
        const target = document.querySelector(button.dataset.copyTarget);
        const status = document.querySelector('[data-copy-status]');

        if (!target) {
            return;
        }

        try {
            await navigator.clipboard.writeText(target.textContent.trim());
            button.textContent = button.dataset.copiedLabel;
            if (status) {
                status.textContent = button.dataset.copiedLabel;
            }
        } catch {
            target.classList.remove('visually-hidden');
            if (status) {
                status.textContent = target.textContent.trim();
            }
        }
    });
});
