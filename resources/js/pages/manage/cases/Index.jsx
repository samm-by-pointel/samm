import { Link, router, usePage } from '@inertiajs/react';
import { ChatCircleDots, FunnelSimple } from '../../../components/icons';
import { useState } from 'react';
import { PageTitle, Pagination } from '../../../components/samm';
import { Alert, Badge, Button, FieldError, Input, Select, StatusBadge } from '../../../components/ui';
import ManageLayout, { PageHeader } from '../../../layouts/ManageLayout';
import { useDateFormatter, useTranslator } from '../../../lib/i18n';

export default function Index({ cases, filters, statuses, priorities, categories }) {
    const { routes, errors } = usePage().props;
    const t = useTranslator();
    const formatDate = useDateFormatter();
    const [values, setValues] = useState({
        status: filters.status ?? '',
        category: filters.category ?? '',
        assignment: filters.assignment ?? 'all',
        priority: filters.priority ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    const set = (name) => (event) => setValues((current) => ({ ...current, [name]: event.target.value }));

    const apply = (event) => {
        event.preventDefault();
        const query = Object.fromEntries(Object.entries(values).filter(([, value]) => value !== ''));
        router.get(routes.cases, query, { preserveState: true, preserveScroll: true });
    };

    const label = (text, htmlFor) => (
        <label htmlFor={htmlFor} className="mb-1.5 block text-[14px] font-semibold text-muted">
            {text}
        </label>
    );

    return (
        <>
            <PageTitle area="manage" title={t('manage.cases.index.title')} />
            <PageHeader title={t('manage.cases.index.title')} />

            <form onSubmit={apply} className="mb-6 rounded-3xl border border-line bg-surface p-5">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:items-end 2xl:grid-cols-[repeat(4,minmax(0,1fr))_repeat(2,minmax(0,0.8fr))_auto]">
                    <div>
                        {label(t('manage.cases.filters.status'), 'status')}
                        <Select id="status" name="status" value={values.status} onChange={set('status')}>
                            <option value="">{t('manage.cases.filters.all')}</option>
                            {statuses.map((status) => (
                                <option key={status.value} value={status.value}>
                                    {status.label}
                                </option>
                            ))}
                        </Select>
                    </div>
                    <div>
                        {label(t('manage.cases.filters.category'), 'category')}
                        <Select id="category" name="category" value={values.category} onChange={set('category')}>
                            <option value="">{t('manage.cases.filters.all')}</option>
                            {categories.map((category) => (
                                <option key={category.value} value={category.value}>
                                    {category.label}
                                </option>
                            ))}
                        </Select>
                    </div>
                    <div>
                        {label(t('manage.cases.filters.assignment'), 'assignment')}
                        <Select id="assignment" name="assignment" value={values.assignment} onChange={set('assignment')}>
                            <option value="all">{t('manage.cases.filters.all')}</option>
                            <option value="mine">{t('manage.cases.filters.mine')}</option>
                            <option value="unassigned">{t('manage.cases.filters.unassigned')}</option>
                        </Select>
                    </div>
                    <div>
                        {label(t('manage.cases.filters.priority'), 'priority')}
                        <Select id="priority" name="priority" value={values.priority} onChange={set('priority')}>
                            <option value="">{t('manage.cases.filters.all')}</option>
                            {priorities.map((priority) => (
                                <option key={priority.value} value={priority.value}>
                                    {priority.label}
                                </option>
                            ))}
                        </Select>
                    </div>
                    <div>
                        {label(t('manage.cases.filters.from'), 'from')}
                        <Input id="from" type="date" name="from" value={values.from} onChange={set('from')} />
                    </div>
                    <div>
                        {label(t('manage.cases.filters.to'), 'to')}
                        <Input id="to" type="date" name="to" value={values.to} onChange={set('to')} />
                    </div>
                    <Button type="submit" variant="secondary">
                        <FunnelSimple size={18} aria-hidden="true" />
                        {t('manage.cases.filters.submit')}
                    </Button>
                </div>
                {Object.values(errors ?? {})[0] && (
                    <div className="mt-3">
                        <FieldError>{Object.values(errors)[0]}</FieldError>
                    </div>
                )}
            </form>

            {cases.data.length === 0 ? (
                <Alert>{t('manage.cases.index.empty')}</Alert>
            ) : (
                <div className="overflow-x-auto rounded-3xl border border-line bg-surface">
                    <table className="w-full min-w-[760px] text-left text-[15px]">
                        <thead className="border-b border-line text-[14px] text-muted">
                            <tr>
                                {['reference', 'category', 'status', 'assignee', 'submitted_at', 'messages'].map((column) => (
                                    <th key={column} scope="col" className="px-5 py-3.5 font-semibold whitespace-nowrap">
                                        {t(`manage.cases.columns.${column}`)}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-line">
                            {cases.data.map((item) => (
                                <tr key={item.url} className="hover:bg-surface-alt">
                                    <td className="px-5 py-4">
                                        <Link href={item.url} className="font-mono font-bold tracking-wide whitespace-nowrap text-brand underline-offset-4 hover:underline">
                                            {item.reference}
                                        </Link>
                                        {item.priority.isHigh && (
                                            <Badge tone="urgent" className="ml-2">
                                                {item.priority.label}
                                            </Badge>
                                        )}
                                    </td>
                                    <td className="px-5 py-4">{item.category}</td>
                                    <td className="px-5 py-4">
                                        <StatusBadge status={item.status} />
                                    </td>
                                    <td className="px-5 py-4">{item.assignee ?? <span className="text-muted">{t('manage.cases.index.unassigned')}</span>}</td>
                                    <td className="px-5 py-4 whitespace-nowrap">
                                        <time dateTime={item.submittedAt}>{formatDate(item.submittedAt, 'short')}</time>
                                    </td>
                                    <td className="px-5 py-4">
                                        {item.unread > 0 ? (
                                            <span className="inline-flex items-center gap-1.5 font-semibold text-brand">
                                                <ChatCircleDots size={18} weight="fill" aria-hidden="true" />
                                                {t.choice('manage.cases.index.unread', item.unread)}
                                            </span>
                                        ) : (
                                            <span className="text-muted">-</span>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            <Pagination meta={cases} />
        </>
    );
}

Index.layout = (page) => <ManageLayout>{page}</ManageLayout>;
