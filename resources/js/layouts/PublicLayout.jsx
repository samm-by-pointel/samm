import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { CaretRight, Menu, ShieldCheck, X } from '../components/icons';
import { LocaleSwitcher, QuickExit, SammSymbol, ThemeToggle } from '../components/samm';
import { useTranslator } from '../lib/i18n';

/**
 * Cadre du site public. L'en-tête reste collé en haut : le bouton « Quitter
 * rapidement » est visible sur chaque écran, à tout moment (charte §12.2).
 * Sur téléphone, tout le reste est rangé dans un menu.
 */
export default function PublicLayout({ children }) {
    const { site, routes } = usePage().props;
    const t = useTranslator();
    const home = routes.home;
    const [open, setOpen] = useState(false);

    // Le menu se referme en changeant de page, et avec Échap (une seule fois :
    // deux Échap restent la sortie rapide).
    useEffect(() => router.on('navigate', () => setOpen(false)), []);
    useEffect(() => {
        if (!open) {
            return undefined;
        }
        const onKeyDown = (event) => event.key === 'Escape' && setOpen(false);
        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, [open]);

    const anchors = [
        ['fonctionnement', 'how'],
        ['reconnaitre', 'recognize'],
        ['garanties', 'guarantees'],
        ['questions', 'faq'],
    ];
    const navLink = 'whitespace-nowrap rounded-full px-3 py-2 text-[15px] font-semibold text-ink hover:bg-surface-alt';
    const menuLink = 'flex min-h-12 items-center rounded-xl px-3 font-semibold hover:bg-surface-alt';

    return (
        <div className="flex min-h-screen flex-col">
            <a href="#contenu" className="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-xl focus:bg-ink focus:px-4 focus:py-3 focus:text-on-ink">
                {t('public.skip_to_content')}
            </a>

            <header className="sticky top-0 z-40 border-b border-line bg-surface/95 backdrop-blur supports-[backdrop-filter]:bg-surface/85">
                <div className="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 sm:px-8 md:gap-6">
                    <Link href={home} className="flex min-h-11 shrink-0 items-center gap-3 text-[26px] leading-none font-bold text-ink">
                        {site.logoUrl && (
                            <>
                                <img src={site.logoUrl} alt="" className="h-9 w-auto max-w-[130px] object-contain" />
                                <span aria-hidden="true" className="h-8 w-px bg-line" />
                            </>
                        )}
                        <SammSymbol size={40} className="text-logo" />
                        <span className="max-md:sr-only">{site.name}</span>
                    </Link>

                    <div className="ml-auto flex items-center gap-2 sm:gap-3">
                        <nav aria-label={t('public.nav.label')} className="hidden md:block">
                            <Link href={routes.trackingLogin} className={navLink}>
                                {t('public.nav.tracking')}
                            </Link>
                        </nav>
                        <div className="hidden md:block">
                            <LocaleSwitcher compact />
                        </div>
                        <div className="hidden md:block">
                            <ThemeToggle />
                        </div>
                        <QuickExit />
                        <button
                            type="button"
                            aria-expanded={open}
                            aria-controls="menu-mobile"
                            onClick={() => setOpen((value) => !value)}
                            className="inline-flex size-11 items-center justify-center rounded-full border border-line text-ink hover:bg-surface-alt md:hidden"
                        >
                            {open ? <X size={22} /> : <Menu size={22} />}
                            <span className="sr-only">{open ? t('public.nav.close') : t('public.nav.menu')}</span>
                        </button>
                    </div>
                </div>

                {open && (
                    <div id="menu-mobile" className="border-t border-line bg-surface px-4 pt-3 pb-5 md:hidden">
                        <nav aria-label={t('public.nav.label')}>
                            <ul className="space-y-1">
                                <li>
                                    <Link href={routes.trackingLogin} className={`${menuLink} text-brand`}>
                                        {t('public.nav.tracking')}
                                    </Link>
                                </li>
                                {anchors.map(([anchor, key]) => (
                                    <li key={anchor}>
                                        <a href={`${home}#${anchor}`} onClick={() => setOpen(false)} className={menuLink}>
                                            {t(`public.nav.${key}`)}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </nav>
                        <div className="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                            <LocaleSwitcher />
                            <ThemeToggle />
                        </div>
                    </div>
                )}
            </header>

            <main id="contenu" className="flex-1">
                {children}
            </main>

            <footer className="bg-ink text-on-ink">
                <div className="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 md:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1.3fr_1fr] lg:gap-0 lg:divide-x lg:divide-white/15">
                    <div className="lg:pr-10">
                        <p className="flex items-center gap-3 text-[24px] font-bold">
                            <SammSymbol size={34} />
                            {site.name}
                        </p>
                        <p className="mt-4 max-w-sm opacity-80">{t('public.footer.about')}</p>
                    </div>
                    <nav aria-labelledby="footer-service" className="lg:px-10">
                        <h2 id="footer-service" className="mb-4 font-bold">
                            {t('public.footer.service_title')}
                        </h2>
                        <ul className="space-y-2.5 opacity-85">
                            <li>
                                <Link href={routes.reportStart} className="inline-flex items-center gap-1 hover:underline">
                                    {t('public.home.report_cta')}
                                    <CaretRight size={14} weight="bold" aria-hidden="true" />
                                </Link>
                            </li>
                            <li>
                                <Link href={routes.trackingLogin} className="inline-flex items-center gap-1 hover:underline">
                                    {t('public.home.track_cta')}
                                    <CaretRight size={14} weight="bold" aria-hidden="true" />
                                </Link>
                            </li>
                            <li>
                                <a href={`${home}#questions`} className="inline-flex items-center gap-1 hover:underline">
                                    {t('public.nav.faq')}
                                    <CaretRight size={14} weight="bold" aria-hidden="true" />
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div className="lg:px-10">
                        <h2 className="mb-4 flex items-center gap-2 font-bold">
                            <ShieldCheck size={22} weight="fill" aria-hidden="true" />
                            {t('public.footer.privacy_title')}
                        </h2>
                        <p className="opacity-80">{t('public.footer.privacy')}</p>
                    </div>
                    <div className="space-y-2 text-[14px] opacity-80 lg:pl-10">
                        <p>{t('public.footer.initiative')}</p>
                        <p>{t('public.footer.license')}</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}

/* Conteneur étroit des formulaires et pages de texte */
export function Narrow({ children, className = '' }) {
    return <div className={`mx-auto max-w-[720px] px-5 py-10 sm:px-8 sm:py-14 ${className}`}>{children}</div>;
}
