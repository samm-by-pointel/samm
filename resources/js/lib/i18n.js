import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';

/**
 * Textes de l'interface : les fichiers lang/ de Laravel, partagés par le serveur.
 * Même syntaxe que __() : clés pointées et remplacements « :nom ».
 */
function lookup(translations, key) {
    return key.split('.').reduce((node, part) => (node && typeof node === 'object' ? node[part] : undefined), translations);
}

function replace(text, params) {
    return Object.entries(params).reduce((result, [name, value]) => result.replaceAll(`:${name}`, String(value)), text);
}

/**
 * Pluriels au format Laravel : « {0} aucun|{1} un|[2,*] :count ».
 */
function choose(text, count, locale) {
    const parts = text.split('|');

    for (const part of parts) {
        const exact = part.match(/^\{(\d+)\}\s?([\s\S]*)$/);
        if (exact && Number(exact[1]) === count) {
            return exact[2];
        }
        const range = part.match(/^\[(\d+|\*),\s*(\d+|\*)\]\s?([\s\S]*)$/);
        if (range) {
            const min = range[1] === '*' ? -Infinity : Number(range[1]);
            const max = range[2] === '*' ? Infinity : Number(range[2]);
            if (count >= min && count <= max) {
                return range[3];
            }
        }
    }

    const plain = parts.filter((part) => !/^(\{\d+\}|\[)/.test(part));
    if (plain.length < 2) {
        return plain[0] ?? parts[parts.length - 1];
    }
    const singular = locale === 'fr' ? count <= 1 : count === 1;

    return singular ? plain[0] : plain[1];
}

export function useTranslator() {
    const { translations, locale } = usePage().props;

    return useMemo(() => {
        const t = (key, params = {}) => {
            const value = lookup(translations, key);

            return typeof value === 'string' ? replace(value, params) : key;
        };

        t.choice = (key, count, params = {}) => {
            const value = lookup(translations, key);

            return typeof value === 'string' ? replace(choose(value, count, locale), { count, ...params }) : key;
        };

        t.has = (key) => typeof lookup(translations, key) === 'string';

        return t;
    }, [translations, locale]);
}

/**
 * Dates affichées dans la langue de l'interface.
 */
export function useDateFormatter() {
    const { locale } = usePage().props;

    return useMemo(() => {
        const make = (options) => new Intl.DateTimeFormat(locale, options);
        const formats = {
            date: make({ day: 'numeric', month: 'long', year: 'numeric' }),
            short: make({ day: 'numeric', month: 'short', year: 'numeric' }),
            dateTime: make({ day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }),
            shortDateTime: make({ day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }),
        };

        return (value, format = 'date') => (value ? formats[format].format(new Date(value)) : '-');
    }, [locale]);
}
