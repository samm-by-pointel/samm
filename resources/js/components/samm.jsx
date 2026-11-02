import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Phone, SignOut } from '@phosphor-icons/react';
import { useEffect } from 'react';
import { leaveQuickly, listenForDoubleEscape } from '../quick-exit';
import { useDateFormatter, useTranslator } from '../lib/i18n';
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
                'inline-flex min-h-11 items-center gap-2 whitespace-nowrap rounded-full bg-ink px-4 text-[15px] font-semibold text-on-ink shadow-[0_2px_10px_rgb(28_26_51/0.25)] hover:brightness-125',
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
                                onClick={() => router.post(routes.locale, { locale: item.code }, { preserveScroll: true })}
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
        <section aria-labelledby="samm-emergency-title" className="rounded-3xl border border-line bg-surface-alt p-5 sm:p-6">
            <h2 id="samm-emergency-title" className="flex items-center gap-2 text-[19px] font-bold">
                <Phone size={22} weight="fill" className="text-brand" aria-hidden="true" />
                {t('public.emergency.title')}
            </h2>
            <p className="mt-1 mb-4 text-muted">{t('public.emergency.lead')}</p>
            {numbers}
        </section>
    );
}

/* Progression du formulaire (charte §12.5) : étape annoncée, « Étape x sur y » */
export function Stepper({ steps, current }) {
    const t = useTranslator();
    const position = steps.findIndex((step) => step.key === current) + 1;
    const currentStep = steps[position - 1];

    return (
        <nav aria-label={t('report_form.progress')}>
            <p className="mb-3 text-[15px] font-semibold text-muted">
                {t('report_form.step_of', { current: position, total: steps.length, label: currentStep?.label ?? '' })}
            </p>
            <ol className="flex gap-1.5">
                {steps.map((step, index) => {
                    const done = index + 1 < position;
                    const isCurrent = step.key === current;

                    return (
                        <li key={step.key} aria-current={isCurrent ? 'step' : undefined} className="flex-1">
                            <span className={cx('block h-1.5 rounded-full', done || isCurrent ? 'bg-brand' : 'bg-line')} />
                            <span className={cx('mt-2 hidden text-[13px] leading-tight md:block', isCurrent ? 'font-semibold text-ink' : 'text-muted')}>
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
 * Le code de suivi, présenté comme un ticket à conserver : c'est l'objet
 * propre à Sàmm, la seule clé qui ramène à un dossier.
 */
export function CodeTicket({ rows, caption, className, notchClass = 'bg-surface' }) {
    return (
        <div className={cx('relative rounded-3xl bg-accent-soft px-6 py-5 text-ink sm:px-8 sm:py-6', className)}>
            {notchClass && (
                <>
                    <span aria-hidden="true" className={cx('absolute top-1/2 -left-3 size-6 -translate-y-1/2 rounded-full', notchClass)} />
                    <span aria-hidden="true" className={cx('absolute top-1/2 -right-3 size-6 -translate-y-1/2 rounded-full', notchClass)} />
                </>
            )}
            <dl className="space-y-4">
                {rows.map((row, index) => (
                    <div key={row.label} className={cx(index > 0 && 'border-t-2 border-dashed border-accent/30 pt-4')}>
                        <dt className="text-[14px] font-semibold text-accent">{row.label}</dt>
                        <dd id={row.id} className="mt-1 font-mono text-[clamp(22px,4vw,30px)] font-bold tracking-[0.12em] break-all">
                            {row.value}
                        </dd>
                    </div>
                ))}
            </dl>
            {caption && <p className="mt-4 text-[14px] text-muted">{caption}</p>}
        </div>
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
