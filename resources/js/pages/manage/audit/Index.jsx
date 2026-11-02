import { Link, router } from '@inertiajs/react';
import { FunnelSimple } from '../../../components/icons';
import { useState } from 'react';
import { PageTitle, Pagination } from '../../../components/samm';
import { Alert, Button, Select } from '../../../components/ui';
import ManageLayout, { PageHeader } from '../../../layouts/ManageLayout';
import { useDateFormatter, useTranslator } from '../../../lib/i18n';

export default function Index({ entries, actions, selectedAction, filteredCase, urls }) {
    const t = useTranslator();
    const formatDate = useDateFormatter();
    const [action, setAction] = useState(selectedAction ?? '');

    const apply = (event) => {
        event.preventDefault();
        const query = { ...(filteredCase ? { case: filteredCase.uuid } : {}), ...(action ? { action } : {}) };
        router.get(urls.index, query, { preserveState: true, preserveScroll: true });
    };

    return (
        <>
            <PageTitle area="manage" title={t('manage.audit.title')} />
            <PageHeader title={t('manage.audit.title')} lead={t('manage.audit.lead')} />

            {filteredCase && (
                <Alert icon={FunnelSimple} className="mb-6" title={t('manage.audit.filtered_case', { reference: filteredCase.reference })}>
                    <Link href={filteredCase.url} className="font-semibold underline underline-offset-4">
                        {t('manage.audit.back_to_case')}
                    </Link>
                    {' · '}
                    <Link href={urls.index} className="font-semibold underline underline-offset-4">
                        {t('manage.audit.clear_filter')}
                    </Link>
                </Alert>
            )}

            <form onSubmit={apply} className="mb-6 flex flex-wrap items-end gap-3">
                <div className="min-w-64">
                    <label htmlFor="action" className="mb-1.5 block text-[14px] font-semibold text-muted">
                        {t('manage.audit.filter_action')}
                    </label>
                    <Select id="action" name="action" value={action} onChange={(event) => setAction(event.target.value)}>
                        <option value="">{t('manage.audit.all_actions')}</option>
                        {actions.map((item) => (
                            <option key={item.value} value={item.value}>
                                {item.label}
                            </option>
                        ))}
                    </Select>
                </div>
                <Button type="submit" variant="secondary">
                    {t('manage.audit.filter_submit')}
                </Button>
            </form>

            {entries.data.length === 0 ? (
                <Alert>{t('manage.audit.empty')}</Alert>
            ) : (
                <div className="overflow-x-auto rounded-3xl border border-line bg-surface">
                    <table className="w-full min-w-[820px] text-left text-[15px]">
                        <thead className="border-b border-line text-[14px] text-muted">
                            <tr>
                                {['date', 'action', 'actor', 'subject', 'reference'].map((column) => (
                                    <th key={column} scope="col" className="px-5 py-3.5 font-semibold whitespace-nowrap">
                                        {t(`manage.audit.columns.${column}`)}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-line">
                            {entries.data.map((entry) => (
                                <tr key={entry.uuid}>
                                    <td className="px-5 py-3.5 whitespace-nowrap">
                                        <time dateTime={entry.at}>{formatDate(entry.at, 'shortDateTime')}</time>
                                    </td>
                                    <td className="px-5 py-3.5 font-semibold">{entry.action}</td>
                                    <td className="px-5 py-3.5">{entry.actor}</td>
                                    <td className="px-5 py-3.5">
                                        {entry.subject ? (
                                            <>
                                                {entry.subject.kind}
                                                {entry.subject.label && ' · '}
                                                {entry.subject.label &&
                                                    (entry.subject.url ? (
                                                        <Link href={entry.subject.url} className="font-mono font-bold text-brand hover:underline">
                                                            {entry.subject.label}
                                                        </Link>
                                                    ) : (
                                                        <span className={entry.subject.code ? 'font-mono' : undefined}>{entry.subject.label}</span>
                                                    ))}
                                            </>
                                        ) : (
                                            '-'
                                        )}
                                    </td>
                                    <td className="px-5 py-3.5 font-mono text-[13px] text-muted">{entry.uuid}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            <Pagination meta={entries} />
        </>
    );
}

Index.layout = (page) => <ManageLayout>{page}</ManageLayout>;
