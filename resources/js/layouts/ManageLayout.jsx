import { Link, router, usePage } from '@inertiajs/react';
import { Buildings, FolderOpen, ListChecks, SignOut, SquaresFour } from '../components/icons';
import { SammSymbol } from '../components/samm';
import { cx } from '../components/ui';
import { useTranslator } from '../lib/i18n';

/* Initiales du compte, pour la pastille du bloc utilisateur */
function initials(name) {
    return (name ?? '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('');
}

/**
 * Espace de gestion : navigation latérale, plus dense et plus large que l'espace public.
 */
export default function ManageLayout({ children }) {
    const page = usePage();
    const { auth, routes, site } = page.props;
    const t = useTranslator();
    const currentPath = page.url.split(/[?#]/)[0];
    const pathOf = (href) => new URL(href, window.location.origin).pathname;

    const items = [
        { href: routes.dashboard, label: t('manage.dashboard.title'), Icon: SquaresFour, show: true, exact: true },
        { href: routes.cases, label: t('manage.layout.cases_link'), Icon: FolderOpen, show: auth?.can.viewCases },
        { href: routes.organization, label: t('manage.layout.organization_link'), Icon: Buildings, show: auth?.can.manageSettings },
        { href: routes.audit, label: t('manage.layout.audit_link'), Icon: ListChecks, show: auth?.can.viewAudit },
    ].filter((item) => item.show);

    const isActive = (item) => (item.exact ? currentPath === pathOf(item.href) : currentPath.startsWith(pathOf(item.href)));

    const logout = (
        <button
            type="button"
            onClick={() => router.post(routes.logout)}
            className="inline-flex min-h-11 items-center gap-2 rounded-xl px-3 text-[15px] font-semibold text-muted hover:bg-surface-alt hover:text-ink"
        >
            <SignOut size={18} aria-hidden="true" />
            {t('manage.layout.logout')}
        </button>
    );

    return (
        <div className="min-h-screen bg-surface-alt lg:grid lg:grid-cols-[264px_1fr]">
            <a href="#contenu" className="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-xl focus:bg-ink focus:px-4 focus:py-3 focus:text-on-ink">
                {t('manage.layout.skip_to_content')}
            </a>

            <aside className="border-b border-line bg-surface lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:border-r lg:border-b-0">
                <div className="flex items-center justify-between gap-3 px-5 py-4 lg:block lg:px-6 lg:py-6">
                    <Link href={routes.dashboard} className="flex items-center gap-3">
                        {site.logoUrl ? (
                            <img src={site.logoUrl} alt="" className="h-9 w-auto max-w-[120px] object-contain" />
                        ) : (
                            <SammSymbol size={36} className="text-logo" />
                        )}
                        <span>
                            <span className="block text-[19px] leading-tight font-bold">{site.appName}</span>
                            <span className="block text-[13px] text-muted">{t('manage.layout.area')}</span>
                        </span>
                    </Link>
                    <div className="lg:hidden">{logout}</div>
                </div>

                <nav aria-label={t('manage.layout.area')} className="overflow-x-auto px-3 pb-3 lg:flex-1 lg:px-4">
                    <ul className="flex gap-1 lg:flex-col">
                        {items.map((item) => {
                            const active = isActive(item);

                            return (
                                <li key={item.href}>
                                    <Link
                                        href={item.href}
                                        aria-current={active ? 'page' : undefined}
                                        className={cx(
                                            'flex min-h-11 items-center gap-3 whitespace-nowrap rounded-xl px-3 text-[16px] font-semibold',
                                            active ? 'bg-brand-soft text-brand' : 'text-muted hover:bg-surface-alt hover:text-ink',
                                        )}
                                    >
                                        <span className={cx('grid size-8 shrink-0 place-items-center rounded-lg', active ? 'bg-surface text-brand' : 'bg-surface-alt')}>
                                            <item.Icon size={20} weight={active ? 'fill' : 'regular'} aria-hidden="true" />
                                        </span>
                                        {item.label}
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </nav>

                <div className="hidden border-t border-line p-4 lg:block">
                    <div className="flex items-center gap-3 px-2">
                        <span aria-hidden="true" className="grid size-10 shrink-0 place-items-center rounded-full bg-brand-soft text-[14px] font-bold text-brand">
                            {initials(auth?.name)}
                        </span>
                        <p className="truncate font-semibold">{auth?.name}</p>
                    </div>
                    <div className="mt-2">{logout}</div>
                </div>
            </aside>

            <main id="contenu" className="min-w-0 px-5 py-8 sm:px-8 lg:px-12 lg:py-10">
                <div className="mx-auto max-w-6xl">{children}</div>
            </main>
        </div>
    );
}

export function PageHeader({ title, lead, actions, children }) {
    return (
        <header className="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div className="min-w-0">
                {children}
                <h1 className="text-[30px] leading-tight font-bold sm:text-[34px]">{title}</h1>
                <span aria-hidden="true" className="mt-3 block h-1 w-12 rounded-full bg-brand" />
                {lead && <p className="mt-3 max-w-2xl text-muted">{lead}</p>}
            </div>
            {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
        </header>
    );
}
