import { Link, usePage } from '@inertiajs/react';
import { CaretRight, ShieldCheck } from '../components/icons';
import { LocaleSwitcher, QuickExit, SammSymbol } from '../components/samm';
import { useTranslator } from '../lib/i18n';

/**
 * Cadre du site public. L'en-tête reste collé en haut : le bouton « Quitter
 * rapidement » est visible sur chaque écran, à tout moment (charte §12.2).
 */
export default function PublicLayout({ children }) {
    const { site, routes } = usePage().props;
    const t = useTranslator();
    const home = routes.home;
    const navLink = 'whitespace-nowrap rounded-full px-3 py-2 text-[15px] font-semibold text-ink hover:bg-surface-alt';

    return (
        <div className="flex min-h-screen flex-col">
            <a href="#contenu" className="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-xl focus:bg-ink focus:px-4 focus:py-3 focus:text-on-ink">
                {t('public.skip_to_content')}
            </a>

            <header className="sticky top-0 z-40 border-b border-line bg-surface/95 backdrop-blur supports-[backdrop-filter]:bg-surface/85">
                <div className="mx-auto flex max-w-7xl items-center gap-6 px-5 py-3 sm:px-8">
                    <Link href={home} className="flex min-h-11 shrink-0 items-center gap-3 text-[26px] leading-none font-bold text-ink">
                        {site.logoUrl && (
                            <>
                                <img src={site.logoUrl} alt="" className="h-9 w-auto max-w-[130px] object-contain" />
                                <span aria-hidden="true" className="h-8 w-px bg-line" />
                            </>
                        )}
                        <SammSymbol size={40} className="text-logo" />
                        <span className="max-sm:sr-only">{site.name}</span>
                    </Link>

                    <nav aria-label={t('public.nav.label')} className="hidden items-center gap-1 xl:flex">
                        <a href={`${home}#fonctionnement`} className={navLink}>
                            {t('public.nav.how')}
                        </a>
                        <a href={`${home}#reconnaitre`} className={navLink}>
                            {t('public.nav.recognize')}
                        </a>
                        <a href={`${home}#garanties`} className={navLink}>
                            {t('public.nav.guarantees')}
                        </a>
                        <a href={`${home}#questions`} className={navLink}>
                            {t('public.nav.faq')}
                        </a>
                        <Link href={routes.trackingLogin} className={navLink}>
                            {t('public.nav.tracking')}
                        </Link>
                    </nav>

                    <div className="ml-auto flex items-center gap-3">
                        <div className="hidden md:block">
                            <LocaleSwitcher compact />
                        </div>
                        <QuickExit />
                    </div>
                </div>
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
                        <div className="pt-4 md:hidden">
                            <LocaleSwitcher />
                        </div>
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
