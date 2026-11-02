import { useCallback, useEffect, useState } from 'react';

/**
 * Mode clair ou sombre. Le choix de la personne est mémorisé sur l'appareil ;
 * sans choix, le thème suit celui du système. Le script du gabarit racine
 * applique déjà le thème avant l'affichage, ce module ne fait que le modifier.
 */
const STORAGE_KEY = 'theme';

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
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const follow = (event) => {
            if (!stored()) {
                document.documentElement.setAttribute('data-theme', event.matches ? 'dark' : 'light');
                setThemeState(current());
            }
        };
        media.addEventListener('change', follow);

        return () => media.removeEventListener('change', follow);
    }, []);

    return [theme, setTheme];
}
