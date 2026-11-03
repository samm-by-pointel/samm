import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Check, LockKey, Moon, Phone, SignOut, Sun } from './icons';
import { Wave } from './illustrations';
import { useEffect } from 'react';
import { leaveQuickly, listenForDoubleEscape } from '../quick-exit';
import { useDateFormatter, useTranslator } from '../lib/i18n';
import { useTheme } from '../lib/theme';
import { cx } from './ui';

/* Symbole Sàmm (charte §7) : il prend currentColor */
export function SammSymbol({ size = 32, className }) {
    return (
        <svg viewBox="0 0 48 48" width={size} height={size} fill="none" aria-hidden="true" focusable="false" className={className}>
            <path d="M8 29 A16 16 0 0 1 40 29" stroke="currentColor" strokeWidth="4.5" strokeLinecap="round" />
            <rect x="15" y="21" width="18" height="13" rx="6.5" fill="currentColor" />
            <path d="M18.5 32 L16 39.5 L25 33.5 Z" fill="currentColor" />
        </svg>
    );
}

/* Drapeaux, toujours accompagnés d'un libellé (charte §6) : couleurs fixes par nature */
export function Flag({ code }) {
    const common = { width: 20, height: 14, 'aria-hidden': true, focusable: false, className: 'rounded-[3px] ring-1 ring-line' };

    if (code === 'fr') {
        return (
            <svg viewBox="0 0 60 40" {...common}>
                <rect width="20" height="40" fill="#0055A4" />
                <rect x="20" width="20" height="40" fill="#FFFFFF" />
                <rect x="40" width="20" height="40" fill="#EF4135" />
            </svg>
        );
    }
    if (code === 'gb') {
        return (
            <svg viewBox="0 0 60 30" {...common}>
                <clipPath id="samm-flag-gb">
                    <rect width="60" height="30" />
                </clipPath>
                <g clipPath="url(#samm-flag-gb)">
                    <rect width="60" height="30" fill="#012169" />
                    <path d="M0 0 L60 30 M60 0 L0 30" stroke="#FFFFFF" strokeWidth="6" />
                    <path d="M0 0 L60 30 M60 0 L0 30" stroke="#C8102E" strokeWidth="2" />
                    <path d="M30 0 V30 M0 15 H60" stroke="#FFFFFF" strokeWidth="10" />
                    <path d="M30 0 V30 M0 15 H60" stroke="#C8102E" strokeWidth="6" />
                </g>
            </svg>
        );
    }
    if (code === 'sn') {
        return (
            <svg viewBox="0 0 60 40" {...common}>
                <rect width="20" height="40" fill="#00853F" />
                <rect x="20" width="20" height="40" fill="#FDEF42" />
                <rect x="40" width="20" height="40" fill="#E31B23" />
                <path d="M30 12 l2.4 7.2 7.6 0 -6.2 4.5 2.4 7.3 -6.2 -4.5 -6.2 4.5 2.4 -7.3 -6.2 -4.5 7.6 0z" fill="#00853F" />
            </svg>
        );
    }

    return null;
}

/**
 * Titre d'onglet. Côté public, un titre neutre défini par l'organisation
 * remplace tout (charte §8) : rien ne trahit la nature du site.
 */
export function PageTitle({ title, area = 'public' }) {
    const { site } = usePage().props;
    const t = useTranslator();

    let full;
    if (area === 'manage') {
        full = [title, t('manage.layout.area'), site.appName].filter(Boolean).join(' · ');
    } else {
        full = site.neutralTitle || [title, site.name].filter(Boolean).join(' · ');
    }

    return <Head title={full} />;
}

/* Sortie rapide - charte §12.2 : identique partout, jamais en couleur d'urgence */
export function QuickExit({ className }) {
    const { site } = usePage().props;
    const t = useTranslator();
    const url = site.quickExitUrl;

    useEffect(() => listenForDoubleEscape(url), [url]);

    return (
        <a
            href={url}
            rel="noreferrer"
            data-quick-exit
            data-quick-exit-url={url}
            onClick={(event) => {
                event.preventDefault();
                leaveQuickly(url);
            }}
            className={cx(
                'inline-flex min-h-11 items-center gap-1.5 whitespace-nowrap rounded-full bg-ink px-3 text-[14px] font-semibold text-on-ink shadow-[0_2px_10px_rgb(28_36_34/0.25)] hover:brightness-125 sm:gap-2 sm:px-4 sm:text-[15px]',
                className,
            )}
        >
            <SignOut size={18} weight="bold" aria-hidden="true" />
            {t('public.quick_exit.label')}
            <kbd aria-label={t('public.quick_exit.shortcut')} className="hidden rounded-md border border-current/40 px-1.5 font-sans text-[12px] font-semibold opacity-80 sm:inline">
                {t('public.quick_exit.key')}
            </kbd>
        </a>
    );
}

/* Choix de la langue sans compte : un bouton par langue */
export function LocaleSwitcher({ compact = false }) {
    const { locale, locales, routes } = usePage().props;
    const t = useTranslator();
    const choose = (code) => router.post(routes.locale, { locale: code }, { preserveScroll: true });

    // Version d'en-tête : un drapeau par langue (charte §6) ; le nom reste lu par les lecteurs d'écran.
    if (compact) {
        return (
            <fieldset className="m-0 flex items-center gap-0.5 rounded-full border border-line p-0.5">
                <legend className="sr-only">{t('public.language.label')}</legend>
                {locales.map((item) => {
                    const active = item.code === locale;

                    return (
                        <button
                            key={item.code}
                            type="button"
                            lang={item.code}
                            aria-current={active ? 'true' : undefined}
                            onClick={() => choose(item.code)}
                            className={cx('inline-flex min-h-10 min-w-10 items-center justify-center rounded-full', active ? 'bg-brand-soft' : 'opacity-60 hover:opacity-100')}
                        >
                            {item.flag && <Flag code={item.flag} />}
                            <span className="sr-only">{item.name}</span>
                        </button>
                    );
                })}
            </fieldset>
        );
    }

    return (
        <fieldset className="m-0 border-0 p-0">
            <legend className="sr-only">{t('public.language.label')}</legend>
            <ul className="flex gap-1">
                {locales.map((item) => {
                    const active = item.code === locale;

                    return (
                        <li key={item.code}>
                            <button
                                type="button"
                                lang={item.code}
                                aria-current={active ? 'true' : undefined}
                                onClick={() => choose(item.code)}
                                className={cx(
                                    'inline-flex min-h-11 min-w-11 items-center justify-center gap-2 rounded-full px-3 text-[14px] font-semibold',
                                    active ? 'bg-brand-soft text-brand' : 'text-muted hover:bg-surface-alt hover:text-ink',
                                )}
                            >
                                {item.flag && <Flag code={item.flag} />}
                                <span className={compact ? 'sr-only' : undefined}>{item.name}</span>
                            </button>
                        </li>
                    );
                })}
            </ul>
        </fieldset>
    );
}

/* Bascule clair / sombre : le libellé annonce le thème vers lequel on bascule */
export function ThemeToggle() {
    const t = useTranslator();
    const [theme, setTheme] = useTheme();
    const next = theme === 'dark' ? 'light' : 'dark';

    return (
        <button
            type="button"
            onClick={() => setTheme(next)}
            aria-label={`${t('public.theme.label')} : ${t(`public.theme.${next}`)}`}
            title={t(`public.theme.${next}`)}
            className="inline-flex size-11 items-center justify-center rounded-full border border-line text-muted hover:bg-surface-alt hover:text-ink"
        >
            {theme === 'dark' ? <Sun size={20} /> : <Moon size={20} />}
        </button>
    );
}

/**
 * « En cas de danger immédiat » : numéros configurés par l'organisation.
 * Une information, pas une alerte : jamais en couleur d'urgence. Rien sans contact.
 */
export function EmergencyContacts({ variant = 'card' }) {
    const { emergencyContacts } = usePage().props;
    const t = useTranslator();

    if (!emergencyContacts?.length) {
        return null;
    }

    const numbers = (
        <ul className="flex flex-wrap gap-2">
            {emergencyContacts.map((contact) => (
                <li key={contact.id}>
                    <a
                        href={`tel:${contact.dialable}`}
                        className="inline-flex min-h-12 items-center gap-3 rounded-full border border-line-strong bg-surface py-1.5 pl-4 pr-1.5 hover:border-ink"
                    >
                        <span className="font-semibold">{contact.label}</span>
                        <span className="rounded-full bg-ink px-3 py-1 font-mono text-[18px] font-bold tracking-wider text-on-ink">{contact.number}</span>
                    </a>
                </li>
            ))}
        </ul>
    );

    if (variant === 'band') {
        return (
            <section aria-labelledby="samm-emergency-title" className="border-y border-line bg-surface-alt">
                <div className="mx-auto flex max-w-6xl flex-col gap-4 px-5 py-6 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-start gap-3">
                        <Phone size={26} weight="fill" className="mt-0.5 shrink-0 text-brand" aria-hidden="true" />
                        <div>
                            <h2 id="samm-emergency-title" className="text-[19px] font-bold">
                                {t('public.emergency.title')}
                            </h2>
                            <p className="text-muted">{t('public.emergency.lead')}</p>
                        </div>
                    </div>
                    {numbers}
                </div>
            </section>
        );
    }

    return (
        <section aria-labelledby="samm-emergency-title" className="rounded-[28px] border border-line bg-surface-alt p-5 sm:p-6">
            <h2 id="samm-emergency-title" className="flex items-center gap-2 text-[19px] font-bold">
                <Phone size={22} weight="fill" className="text-brand" aria-hidden="true" />
                {t('public.emergency.title')}
            </h2>
            <p className="mt-1 mb-4 text-muted">{t('public.emergency.lead')}</p>
            {numbers}
        </section>
    );
}

/* Progression du formulaire (charte §12.5) : pastilles reliées, étape faite cochée, « Étape x sur y » */
export function Stepper({ steps, current }) {
    const t = useTranslator();
    const position = steps.findIndex((step) => step.key === current) + 1;
    const currentStep = steps[position - 1];

    return (
        <nav aria-label={t('report_form.progress')}>
            <p className="mb-4 text-[15px] font-semibold text-brand">
                {t('report_form.step_of', { current: position, total: steps.length, label: currentStep?.label ?? '' })}
            </p>
            <ol className="flex items-start">
                {steps.map((step, index) => {
                    const done = index + 1 < position;
                    const isCurrent = step.key === current;
                    const last = index === steps.length - 1;

                    return (
                        <li key={step.key} aria-current={isCurrent ? 'step' : undefined} className={cx('relative flex flex-col items-center', last ? 'flex-none' : 'flex-1')}>
                            {!last && <span aria-hidden="true" className={cx('absolute top-4 left-1/2 h-0.5 w-full', done ? 'bg-brand' : 'bg-line')} />}
                            <span
                                aria-hidden="true"
                                className={cx(
                                    'relative grid size-8 place-items-center rounded-full border-2 text-[14px] font-bold',
                                    done && 'border-brand bg-brand text-on-brand',
                                    isCurrent && 'border-brand bg-surface text-brand ring-4 ring-brand-soft',
                                    !done && !isCurrent && 'border-line bg-surface text-muted',
                                )}
                            >
                                {done ? <Check size={16} weight="bold" /> : index + 1}
                            </span>
                            <span className={cx('mt-2 hidden max-w-24 text-center text-[12px] leading-tight md:block', isCurrent ? 'font-semibold text-ink' : 'text-muted')}>
                                {step.label}
                            </span>
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}

/**
 * Le code de suivi, présenté comme une carte à conserver, fermée par un cadenas :
 * c'est l'objet propre à Sàmm, la seule clé qui ramène à un dossier.
 */
export function CodeTicket({ rows, caption, title, className }) {
    return (
        <div className={cx('overflow-hidden rounded-[28px] border border-line bg-surface text-ink', className)}>
            <div className="flex items-center justify-between gap-4 bg-brand-soft px-6 py-3 sm:px-8">
                <span className="text-[14px] font-bold text-brand">{title ?? rows[0]?.label}</span>
                <span aria-hidden="true" className="grid size-9 place-items-center rounded-full bg-ink text-on-ink">
                    <LockKey size={18} weight="fill" />
                </span>
            </div>
            <dl className="space-y-5 px-6 py-5 sm:px-8 sm:py-6">
                {rows.map((row, index) => (
                    <div key={row.label} className={cx(index > 0 && 'border-t border-dashed border-line-strong/50 pt-5')}>
                        {(index > 0 || title) && <dt className="text-[14px] font-semibold text-muted">{row.label}</dt>}
                        {index === 0 && !title && <dt className="sr-only">{row.label}</dt>}
                        <dd id={row.id} className="mt-1 font-mono text-[clamp(22px,4vw,30px)] font-bold tracking-[0.12em] break-all">
                            {row.value}
                        </dd>
                    </div>
                ))}
                {caption && <p className="text-[14px] text-muted">{caption}</p>}
            </dl>
        </div>
    );
}

/**
 * Bandeau d'en-tête des pages publiques, dans le langage de l'accueil : fond
 * vert pâle, titre, phrase d'introduction, et une illustration ou un élément à droite.
 */
export function PublicHero({ eyebrow, title, lead, aside, children, narrow = true }) {
    return (
        <section className="relative overflow-hidden bg-surface-alt">
            <div className={cx('relative mx-auto grid items-center gap-8 px-5 pt-10 pb-20 sm:px-8 sm:pt-14 sm:pb-24', narrow ? 'max-w-[960px] lg:grid-cols-[minmax(0,1fr)_240px]' : 'max-w-7xl lg:grid-cols-[minmax(0,1fr)_320px]', !aside && 'lg:grid-cols-1')}>
                <div className="min-w-0">
                    {eyebrow}
                    <h1 className="text-[clamp(30px,4.5vw,44px)] leading-tight font-bold tracking-[-0.01em]">{title}</h1>
                    {lead && <p className="mt-4 max-w-2xl text-[18px] leading-relaxed">{lead}</p>}
                    {children}
                </div>
                {aside && <div className="hidden justify-self-end lg:block">{aside}</div>}
            </div>
            <Wave className="absolute bottom-0 left-0 h-10 w-full sm:h-14" />
        </section>
    );
}

/* Fil de messages (charte §12.8) : l'équipe à gauche, la personne qui signale à droite */
export function MessageThread({ messages, emptyLabel }) {
    const t = useTranslator();
    const formatDate = useDateFormatter();

    if (!messages.length) {
        return <p className="rounded-2xl border border-dashed border-line-strong p-5 text-muted">{emptyLabel ?? t('tracking.thread.empty')}</p>;
    }

    return (
        <div role="log" aria-label={t('tracking.thread.label')} className="flex flex-col gap-3">
            {messages.map((message, index) => (
                <div
                    key={message.id ?? index}
                    className={cx(
                        'max-w-[85%] rounded-3xl px-5 py-3.5 sm:max-w-[75%]',
                        message.fromReporter ? 'self-end rounded-br-md bg-accent-soft' : 'self-start rounded-bl-md border border-line bg-surface',
                    )}
                >
                    <p className="whitespace-pre-line">{message.body}</p>
                    <p className="mt-1.5 text-[13px] text-muted">
                        {message.author} · <time dateTime={message.createdAt}>{formatDate(message.createdAt, 'shortDateTime')}</time>
                    </p>
                </div>
            ))}
        </div>
    );
}

/* Pagination Laravel (paginate()->toArray()) */
export function Pagination({ meta }) {
    const t = useTranslator();

    if (!meta || meta.last_page <= 1) {
        return null;
    }

    return (
        <nav aria-label={t('manage.pagination.label')} className="mt-6 flex items-center justify-between gap-4">
            <span className="text-[15px] text-muted">{t('manage.pagination.summary', { from: meta.from, to: meta.to, total: meta.total })}</span>
            <div className="flex gap-2">
                {meta.prev_page_url && (
                    <Link href={meta.prev_page_url} preserveScroll className="inline-flex min-h-11 items-center gap-1.5 rounded-xl border border-line-strong px-4 font-semibold hover:bg-surface-alt">
                        <ArrowLeft size={18} aria-hidden="true" />
                        {t('manage.pagination.previous')}
                    </Link>
                )}
                {meta.next_page_url && (
                    <Link href={meta.next_page_url} preserveScroll className="inline-flex min-h-11 items-center gap-1.5 rounded-xl border border-line-strong px-4 font-semibold hover:bg-surface-alt">
                        {t('manage.pagination.next')}
                        <ArrowRight size={18} aria-hidden="true" />
                    </Link>
                )}
            </div>
        </nav>
    );
}
