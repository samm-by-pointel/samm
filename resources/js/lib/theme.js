import { useCallback, useEffect, useState } from 'react';

/**
 * Mode clair ou sombre. Le choix de la personne est mémorisé sur l'appareil
 * (localStorage, rien n'est envoyé au serveur) ; sans choix, le thème suit
 * celui du système. L'attribut data-theme est lu par app.css et par le CSS
 * d'organisation (BrandingCss).
 */
const STORAGE_KEY = 'theme';
const media = () => window.matchMedia('(prefers-color-scheme: dark)');

/**
 * À appeler au démarrage : pose le thème mémorisé, sinon celui du système,
 * et suit le système tant qu'aucun choix n'est mémorisé.
 */
export function applyStoredTheme() {
    const apply = () => document.documentElement.setAttribute('data-theme', stored() ?? (media().matches ? 'dark' : 'light'));
    apply();
    media().addEventListener('change', apply);
}

function current() {
    return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
}

function stored() {
    try {
        const value = localStorage.getItem(STORAGE_KEY);
        return value === 'light' || value === 'dark' ? value : null;
    } catch {
        return null;
    }
}

export function useTheme() {
    const [theme, setThemeState] = useState(current);

    const setTheme = useCallback((value) => {
        document.documentElement.setAttribute('data-theme', value);
        try {
            localStorage.setItem(STORAGE_KEY, value);
        } catch {
            // Stockage indisponible (navigation privée stricte) : le choix vaut pour cette page.
        }
        setThemeState(value);
    }, []);

    useEffect(() => {
        const query = media();
        const follow = () => setThemeState(current());
        query.addEventListener('change', follow);

        return () => query.removeEventListener('change', follow);
    }, []);

    return [theme, setTheme];
}
