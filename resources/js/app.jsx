import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

import { applyStoredTheme } from './lib/theme';

// Thème choisi par la personne, sinon celui du système (voir lib/theme.js).
applyStoredTheme();

const pages = import.meta.glob('./pages/**/*.jsx');

createInertiaApp({
    resolve: (name) => pages[`./pages/${name}.jsx`](),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#0E5A55',
        showSpinner: false,
    },
});
