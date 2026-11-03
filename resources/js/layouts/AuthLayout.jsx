import { usePage } from '@inertiajs/react';
import { Leaves } from '../components/illustrations';
import { SammSymbol } from '../components/samm';
import { useTranslator } from '../lib/i18n';

/**
 * Connexion à l'espace de gestion : une carte centrée, sans navigation,
 * sur le fond vert pâle de l'accueil.
 */
export default function AuthLayout({ children }) {
    const { site } = usePage().props;
    const t = useTranslator();

    return (
        <div className="relative flex min-h-screen flex-col items-center overflow-hidden bg-surface-alt px-5 py-12 sm:py-20">
            <Leaves className="absolute -top-6 -right-6 w-40 opacity-60 sm:right-10 sm:w-48" />
            <Leaves className="absolute -bottom-16 -left-10 w-40 rotate-180 opacity-40 sm:w-48" />
            <div className="relative mb-8 flex items-center gap-3">
                <SammSymbol size={40} className="text-logo" />
                <span>
                    <span className="block text-[24px] leading-tight font-bold">{site.appName}</span>
                    <span className="block text-[14px] text-muted">{t('manage.layout.area')}</span>
                </span>
            </div>
            <main id="contenu" className="relative w-full max-w-[460px] rounded-[28px] border border-line bg-surface p-7 sm:p-9">
                {children}
            </main>
        </div>
    );
}
