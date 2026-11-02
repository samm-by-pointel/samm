import { Link, usePage } from '@inertiajs/react';
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

    return (
        <div className="flex min-h-screen flex-col">
            <a href="#contenu" className="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-xl focus:bg-ink focus:px-4 focus:py-3 focus:text-on-ink">
                {t('public.skip_to_content')}
            </a>

            <header className="sticky top-0 z-40 border-b border-line bg-surface/95 backdrop-blur supports-[backdrop-filter]:bg-surface/85">
                <div className="mx-auto flex max-w-6xl items-center gap-6 px-5 py-2.5 sm:px-8">
                    <Link href={home} className="flex min-h-11 shrink-0 items-center gap-2.5 text-[20px] font-bold text-ink">
                        {site.logoUrl && (
                            <>
                                <img src={site.logoUrl} alt="" className="h-8 w-auto max-w-[120px] object-contain" />
                                <span aria-hidden="true" className="h-7 w-px bg-line" />
                            </>
                        )}
                        <SammSymbol size={30} className="text-brand" />
                        <span className="max-sm:sr-only">{site.name}</span>
                    </Link>

                    <nav aria-label={t('public.nav.label')} className="hidden items-center gap-1 xl:flex">
                        <a href={`${home}#fonctionnement`} className="whitespace-nowrap rounded-full px-3.5 py-2 text-[16px] text-ink hover:bg-surface-alt">
                            {t('public.nav.how')}
                        </a>
                        <a href={`${home}#garanties`} className="whitespace-nowrap rounded-full px-3.5 py-2 text-[16px] text-ink hover:bg-surface-alt">
                            {t('public.nav.guarantees')}
                        </a>
                        <a href={`${home}#questions`} className="whitespace-nowrap rounded-full px-3.5 py-2 text-[16px] text-ink hover:bg-surface-alt">
                            {t('public.nav.faq')}
                        </a>
                        <Link href={routes.trackingLogin} className="whitespace-nowrap rounded-full px-3.5 py-2 text-[16px] text-ink hover:bg-surface-alt">
                            {t('public.nav.tracking')}
                        </Link>
                    </nav>

                    <div className="ml-auto flex items-center gap-2">
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
                <div className="mx-auto grid max-w-6xl gap-10 px-5 pt-14 pb-10 sm:px-8 md:grid-cols-[2fr_1fr_1.4fr]">
                    <div>
                        <p className="flex items-center gap-2.5 text-[20px] font-bold">
                            <SammSymbol size={28} />
                            {site.name}
                        </p>
                        <p className="mt-3 max-w-sm opacity-75">{t('public.footer.about')}</p>
                    </div>
                    <nav aria-labelledby="footer-service">
                        <h2 id="footer-service" className="mb-3 font-bold">
                            {t('public.footer.service_title')}
                        </h2>
                        <ul className="space-y-2 opacity-80">
                            <li>
                                <Link href={routes.reportStart} className="hover:underline">
                                    {t('public.home.report_cta')}
                                </Link>
                            </li>
                            <li>
                                <Link href={routes.trackingLogin} className="hover:underline">
                                    {t('public.home.track_cta')}
                                </Link>
                            </li>
                            <li>
                                <a href={`${home}#questions`} className="hover:underline">
                                    {t('public.nav.faq')}
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div>
                        <h2 className="mb-3 font-bold">{t('public.footer.privacy_title')}</h2>
                        <p className="opacity-75">{t('public.footer.privacy')}</p>
                        <div className="mt-5 md:hidden">
                            <LocaleSwitcher />
                        </div>
                    </div>
                </div>
                <div className="mx-auto flex max-w-6xl flex-wrap justify-between gap-2 border-t border-white/15 px-5 py-6 text-[14px] opacity-70 sm:px-8">
                    <span>{t('public.footer.initiative')}</span>
                    <span>{t('public.footer.license')}</span>
                </div>
            </footer>
        </div>
    );
}

/* Conteneur étroit des formulaires et pages de texte */
export function Narrow({ children, className = '' }) {
    return <div className={`mx-auto max-w-[720px] px-5 py-10 sm:px-8 sm:py-14 ${className}`}>{children}</div>;
}
