/*
 * Illustrations de l'accueil, en SVG en ligne : elles prennent les jetons de la
 * charte (couleur de l'organisation, mode sombre) et ne chargent aucun fichier.
 * Toutes décoratives : aria-hidden, le texte voisin porte le sens.
 */

const svgProps = { 'aria-hidden': true, focusable: false, xmlns: 'http://www.w3.org/2000/svg' };

/* Tache lilas derrière chaque illustration d'étape */
function Blob() {
    return <path d="M24 70c-6-28 22-52 62-56 44-4 92 6 102 36 10 32-18 62-60 68-46 6-98-12-104-48Z" className="fill-brand-soft" />;
}

function Sparkles() {
    return (
        <g className="stroke-brand" strokeWidth="3" strokeLinecap="round">
            <path d="M166 28l6-8M174 40l9-2M160 18l1-9" />
        </g>
    );
}

/* Étape 1 : un ordinateur portable avec un formulaire à l'écran */
export function IllustrationDescribe({ className }) {
    return (
        <svg viewBox="0 0 210 140" className={className} {...svgProps}>
            <Blob />
            <Sparkles />
            <rect x="52" y="22" width="106" height="72" rx="7" className="fill-surface stroke-ink" strokeWidth="4" />
            <rect x="62" y="32" width="86" height="52" rx="3" className="fill-brand-soft" />
            <rect x="70" y="40" width="34" height="6" rx="3" className="fill-brand" />
            <rect x="70" y="52" width="68" height="5" rx="2.5" className="fill-line-strong" opacity=".55" />
            <rect x="70" y="62" width="56" height="5" rx="2.5" className="fill-line-strong" opacity=".55" />
            <rect x="70" y="72" width="44" height="5" rx="2.5" className="fill-line-strong" opacity=".55" />
            <path d="M38 98h134l-8 12H46z" className="fill-ink" />
            <rect x="92" y="98" width="26" height="4" rx="2" className="fill-surface" opacity=".5" />
        </svg>
    );
}

/* Étape 2 : la carte du code de suivi, fermée par un cadenas */
export function IllustrationCode({ className }) {
    return (
        <svg viewBox="0 0 210 140" className={className} {...svgProps}>
            <Blob />
            <rect x="46" y="30" width="112" height="70" rx="9" className="fill-surface stroke-line-strong" strokeWidth="2" />
            <rect x="46" y="30" width="112" height="16" rx="9" className="fill-brand-soft" />
            <text x="102" y="74" textAnchor="middle" className="fill-ink font-mono" fontSize="16" fontWeight="700" letterSpacing="1.5">
                SMM-7KQ4
            </text>
            <rect x="60" y="84" width="58" height="4" rx="2" className="fill-line-strong" opacity=".5" />
            <path d="M146 78v-8a12 12 0 0 1 24 0v8" fill="none" className="stroke-ink" strokeWidth="5" strokeLinecap="round" />
            <rect x="138" y="76" width="40" height="32" rx="6" className="fill-ink" />
            <circle cx="158" cy="90" r="4" className="fill-surface" />
            <rect x="156.5" y="91" width="3" height="8" rx="1.5" className="fill-surface" />
        </svg>
    );
}

/* Étape 3 : un calendrier, la suite du dossier, avec une coche */
export function IllustrationFollow({ className }) {
    const cells = [];
    for (let row = 0; row < 3; row += 1) {
        for (let col = 0; col < 5; col += 1) {
            cells.push(<rect key={`${row}-${col}`} x={68 + col * 15} y={56 + row * 13} width="10" height="8" rx="2" className={row === 1 && col === 2 ? 'fill-brand' : 'fill-line-strong'} opacity={row === 1 && col === 2 ? 1 : 0.45} />);
        }
    }

    return (
        <svg viewBox="0 0 210 140" className={className} {...svgProps}>
            <Blob />
            <Sparkles />
            <rect x="56" y="26" width="98" height="82" rx="9" className="fill-surface stroke-ink" strokeWidth="4" />
            <path d="M56 35a9 9 0 0 1 9-9h80a9 9 0 0 1 9 9v10H56z" className="fill-ink" />
            <rect x="76" y="18" width="6" height="16" rx="3" className="fill-ink" />
            <rect x="128" y="18" width="6" height="16" rx="3" className="fill-ink" />
            {cells}
            <circle cx="152" cy="100" r="19" className="fill-ink" />
            <path d="M143 100l6 6 12-13" fill="none" className="stroke-surface" strokeWidth="4.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

/* Feuilles décoratives de l'accroche */
export function Leaves({ className }) {
    return (
        <svg viewBox="0 0 160 220" className={className} {...svgProps}>
            <path d="M150 6C96 14 50 52 44 110c-3 30 18 44 42 36 46-16 70-78 64-140Z" className="fill-brand" opacity=".85" />
            <path d="M92 150c-32 4-58 24-62 52 16 10 46 2 62-16 10-12 8-28 0-36Z" className="fill-brand" opacity=".3" />
        </svg>
    );
}

/* Bas de l'accroche en vague : la section suivante remonte en courbe */
export function Wave({ className }) {
    return (
        <svg viewBox="0 0 1440 120" preserveAspectRatio="none" className={className} {...svgProps}>
            <path d="M0 120V70c260 38 520 52 780 28 250-22 450-62 660-98v120z" className="fill-surface" />
        </svg>
    );
}
