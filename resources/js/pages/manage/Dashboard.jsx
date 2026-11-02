import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, Tray, UserCircle, Folders } from '../../components/icons';
import { PageTitle } from '../../components/samm';
import { Alert } from '../../components/ui';
import ManageLayout, { PageHeader } from '../../layouts/ManageLayout';
import { useTranslator } from '../../lib/i18n';

export default function Dashboard({ canViewCases, counters, urls }) {
    const { auth } = usePage().props;
    const t = useTranslator();

    const tiles = [
        { key: 'mine_open', href: urls.mine, Icon: UserCircle },
        { key: 'unassigned_open', href: urls.unassigned, Icon: Tray },
        { key: 'visible_total', href: urls.all, Icon: Folders },
    ];

    return (
        <>
            <PageTitle area="manage" title={t('manage.dashboard.title')} />
            <PageHeader title={t('manage.dashboard.welcome', { name: auth.name })} lead={t('manage.dashboard.title')} />

            {canViewCases ? (
                <div className="grid gap-4 md:grid-cols-3">
                    {tiles.map(({ key, href, Icon }) => (
                        <Link key={key} href={href} className="group flex flex-col rounded-3xl border border-line bg-surface p-6 hover:border-brand">
                            <Icon size={28} className="text-brand" aria-hidden="true" />
                            <span className="mt-6 text-[44px] leading-none font-bold tabular-nums">{counters[key]}</span>
                            <span className="mt-2 flex items-center justify-between gap-2 font-semibold text-muted group-hover:text-ink">
                                {t(`manage.dashboard.${key}`)}
                                <ArrowRight size={18} aria-hidden="true" />
                            </span>
                        </Link>
                    ))}
                </div>
            ) : (
                <Alert>{t('manage.dashboard.no_case_access')}</Alert>
            )}
        </>
    );
}

Dashboard.layout = (page) => <ManageLayout>{page}</ManageLayout>;
