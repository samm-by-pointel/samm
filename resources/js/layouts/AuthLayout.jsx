import { usePage } from '@inertiajs/react';
import { SammSymbol } from '../components/samm';
import { useTranslator } from '../lib/i18n';

/**
 * Connexion à l'espace de gestion : une carte centrée, sans navigation.
 */
export default function AuthLayout({ children }) {
    const { site } = usePage().props;
    const t = useTranslator();

    return (
        <div className="flex min-h-screen flex-col items-center bg-surface-alt px-5 py-12 sm:py-20">
            <div className="mb-8 flex items-center gap-3">
                <SammSymbol size={36} className="text-brand" />
                <span>
                    <span className="block text-[22px] leading-tight font-bold">{site.appName}</span>
                    <span className="block text-[14px] text-muted">{t('manage.layout.area')}</span>
                </span>
            </div>
            <main id="contenu" className="w-full max-w-[460px] rounded-3xl border border-line bg-surface p-7 sm:p-9">
                {children}
            </main>
        </div>
    );
}
