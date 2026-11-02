import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

/**
 * Thème : les jetons suivent déjà prefers-color-scheme en CSS ; l'attribut sert
 * aux couleurs de l'organisation (BrandingCss), qui ciblent le même sélecteur.
 */
function syncTheme(mediaQuery) {
    document.documentElement.setAttribute('data-bs-theme', mediaQuery.matches ? 'dark' : 'light');
}

const darkScheme = window.matchMedia('(prefers-color-scheme: dark)');
syncTheme(darkScheme);
darkScheme.addEventListener('change', syncTheme);

const pages = import.meta.glob('./pages/**/*.jsx');

createInertiaApp({
    resolve: (name) => pages[`./pages/${name}.jsx`](),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#4636A8',
        showSpinner: false,
    },
});
