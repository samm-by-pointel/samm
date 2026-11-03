import { Link, useForm, useHttp, usePage } from '@inertiajs/react';
import { ArrowLeft, Eye, ListChecks, NotePencil, PaperPlaneRight, Warning } from '../../../components/icons';
import { useState } from 'react';
import { MessageThread, PageTitle } from '../../../components/samm';
import { Alert, Badge, Button, DefinitionList, Field, Select, StatusBadge, Textarea, cx } from '../../../components/ui';
import ManageLayout from '../../../layouts/ManageLayout';
import { useDateFormatter, useTranslator } from '../../../lib/i18n';

export default function Show(props) {
    const { case: file, report, identity, histories, notes, messages, can, urls } = props;
    const { status } = usePage().props;
    const t = useTranslator();
    const formatDate = useDateFormatter();
    const hasActions = can.assign || can.updateStatus || can.changePriority;

    const yesNo = (value) => (value === null || value === undefined ? t('report_form.unknown') : value ? t('report_form.yes') : t('report_form.no'));

    return (
        <>
            <PageTitle area="manage" title={file.reference} />

            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <Link href={urls.back} className="inline-flex min-h-11 items-center gap-2 font-semibold text-brand hover:underline">
                    <ArrowLeft size={18} aria-hidden="true" />
                    {t('manage.cases.show.back')}
                </Link>
                {urls.audit && (
                    <Link href={urls.audit} className="inline-flex min-h-11 items-center gap-2 font-semibold text-muted hover:text-ink">
                        <ListChecks size={18} aria-hidden="true" />
                        {t('manage.cases.show.audit_link')}
                    </Link>
                )}
            </div>

            <header className="flex flex-wrap items-center gap-3 rounded-[28px] bg-brand-soft px-6 py-5">
                <h1 className="font-mono text-[30px] font-bold tracking-[0.06em] sm:text-[34px]">{file.reference}</h1>
                <StatusBadge status={file.status} />
                {file.priority.isHigh && <Badge tone="urgent">{file.priority.label}</Badge>}
            </header>

            <div className="mt-4 rounded-[28px] border border-line bg-surface p-6">
                <DefinitionList
                    className="lg:grid-cols-3"
                    items={[
                        { term: t('manage.cases.show.category'), detail: file.category },
                        { term: t('manage.cases.show.confidentiality'), detail: file.confidentiality },
                        { term: t('manage.cases.show.reporter'), detail: file.reporter },
                        { term: t('manage.cases.show.assignee'), detail: file.assignee ?? t('manage.cases.index.unassigned') },
                        { term: t('manage.cases.show.submitted_at'), detail: formatDate(file.submittedAt, 'dateTime') },
                        { term: t('manage.cases.show.last_activity'), detail: formatDate(file.lastActivityAt, 'dateTime') },
                    ]}
                />
            </div>

            {file.immediateDanger && (
                <Alert tone="urgent" icon={Warning} className="mt-4" title={t('manage.cases.show.danger_title')}>
                    {t('manage.cases.show.danger_help')}
                </Alert>
            )}

            {status && (
                <Alert tone="succes" className="mt-4">
                    {status}
                </Alert>
            )}

            <div className={cx('mt-8 grid gap-8', hasActions && 'xl:grid-cols-[minmax(0,1fr)_340px]')}>
                <div className="min-w-0 space-y-8">
                    <IdentityPanel identity={identity} />

                    <section aria-labelledby="report-title" className="rounded-[28px] border border-line bg-surface p-6 sm:p-8">
                        <h2 id="report-title" className="text-[21px] font-bold">
                            {t('manage.cases.show.report_title')}
                        </h2>
                        <p className="mt-4 text-[17px] leading-relaxed whitespace-pre-line">{report.description}</p>
                        <DefinitionList
                            className="mt-8 border-t border-line pt-6"
                            items={[
                                { term: t('report_form.description.facts_date'), detail: report.factsDate ? formatDate(report.factsDate) : '-' },
                                { term: t('report_form.description.facts_period'), detail: report.factsPeriod || '-' },
                                { term: t('report_form.description.location'), detail: report.location || '-' },
                                { term: t('report_form.description.is_recurring'), detail: yesNo(report.isRecurring) },
                                { term: t('report_form.description.is_ongoing'), detail: yesNo(report.isOngoing) },
                                { term: t('report_form.people.people_involved'), detail: report.peopleInvolved || '-', wide: true },
                                { term: t('report_form.people.witnesses'), detail: report.witnesses || '-', wide: true },
                            ]}
                        />
                    </section>

                    <section aria-labelledby="messages-title">
                        <h2 id="messages-title" className="mb-4 text-[21px] font-bold">
                            {t('manage.cases.show.messages_title')}
                        </h2>
                        <MessageThread messages={messages} />
                        {can.reply && <ReplyForm url={urls.message} />}
                    </section>

                    <section aria-labelledby="notes-title">
                        <h2 id="notes-title" className="text-[21px] font-bold">
                            {t('manage.cases.notes.title')}
                        </h2>
                        <p className="mt-1 mb-4 text-[15px] text-muted">{t('manage.cases.notes.help')}</p>
                        {notes.length === 0 ? (
                            <p className="text-muted">{t('manage.cases.notes.empty')}</p>
                        ) : (
                            <ol className="space-y-3">
                                {notes.map((note, index) => (
                                    <li key={index} className="rounded-2xl border-l-4 border-attente bg-attente-soft/60 p-4">
                                        <p className="whitespace-pre-line">{note.body}</p>
                                        <p className="mt-2 text-[13px] text-muted">
                                            {formatDate(note.at, 'dateTime')} · {note.author}
                                        </p>
                                    </li>
                                ))}
                            </ol>
                        )}
                        {can.addNote && <NoteForm url={urls.note} />}
                    </section>

                    <section aria-labelledby="history-title">
                        <h2 id="history-title" className="mb-4 text-[21px] font-bold">
                            {t('manage.cases.show.history_title')}
                        </h2>
                        {histories.length === 0 ? (
                            <p className="text-muted">{t('manage.cases.show.history_empty')}</p>
                        ) : (
                            <ol className="relative space-y-5 border-l-2 border-line pl-6">
                                {histories.map((history, index) => (
                                    <li key={index} className="relative">
                                        <span aria-hidden="true" className="absolute top-1.5 -left-[31px] size-3 rounded-full bg-brand ring-4 ring-surface-alt" />
                                        <p>
                                            <span className="font-semibold">{history.to}</span>
                                            {history.from && <span className="text-muted"> ({t('manage.cases.show.history_from', { status: history.from })})</span>}
                                        </p>
                                        <p className="text-[13px] text-muted">
                                            {formatDate(history.at, 'dateTime')} · {history.by}
                                        </p>
                                        {history.comment && <p className="mt-1 whitespace-pre-line">{history.comment}</p>}
                                    </li>
                                ))}
                            </ol>
                        )}
                    </section>
                </div>

                {hasActions && (
                    <aside aria-labelledby="actions-title" className="xl:sticky xl:top-8 xl:self-start">
                        <div className="space-y-4">
                            <h2 id="actions-title" className="text-[19px] font-bold">
                                {t('manage.cases.actions.title')}
                            </h2>
                            {can.assign && <AssignForm url={urls.assign} users={props.assignableUsers} current={file.assigneeUuid} />}
                            {can.updateStatus && <StatusForm url={urls.status} transitions={props.allowedTransitions} />}
                            {can.changePriority && <PriorityForm url={urls.priority} priorities={props.priorities} current={file.priority.value} />}
                        </div>
                    </aside>
                )}
            </div>
        </>
    );
}

/**
 * Identité de la personne qui signale (charte §12.10) : masquée par défaut,
 * révélée après un motif, pour cette page seulement. Elle n'est gardée qu'en
 * mémoire : un rechargement la masque à nouveau.
 */
function IdentityPanel({ identity }) {
    const t = useTranslator();
    const [open, setOpen] = useState(false);
    const http = useHttp({ reason: '' });
    const revealed = http.response?.identity;

    return (
        <section aria-labelledby="identity-title" className="rounded-[28px] border-2 border-dashed border-line-strong bg-surface p-6">
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 id="identity-title" className="text-[19px] font-bold">
                        {t('manage.cases.identity.title')}
                    </h2>
                    {!identity.allowed && <p className="mt-1 text-muted">{t('manage.cases.identity.none', { level: identity.level })}</p>}
                    {identity.allowed && !revealed && (
                        <>
                            <p aria-label={t('manage.cases.identity.masked')} className="mt-2 font-mono text-[20px] tracking-[0.3em] text-muted">
                                •••••• ••••••
                            </p>
                            <p className="mt-1 text-[14px] text-muted">{t('manage.cases.identity.confidential_help')}</p>
                        </>
                    )}
                </div>

                {identity.allowed && !revealed && identity.canView && !open && (
                    <Button variant="secondary" onClick={() => setOpen(true)}>
                        <Eye size={20} aria-hidden="true" />
                        {t('manage.cases.identity.reveal')}
                    </Button>
                )}
                {identity.allowed && !identity.canView && (
                    <p className="text-[14px] text-muted">{identity.consentGranted ? t('manage.cases.identity.forbidden') : t('manage.cases.identity.no_consent')}</p>
                )}
            </div>

            {revealed && (
                <div className="mt-4 rounded-2xl bg-surface-alt p-5">
                    <DefinitionList
                        className="lg:grid-cols-3"
                        items={[
                            { term: t('manage.cases.identity.full_name'), detail: revealed.fullName },
                            { term: t('manage.cases.identity.email'), detail: revealed.email || '-' },
                            { term: t('manage.cases.identity.phone'), detail: revealed.phone || '-' },
                        ]}
                    />
                    <p className="mt-4 text-[14px] text-muted">{t('manage.cases.identity.revealed_help')}</p>
                </div>
            )}

            {open && !revealed && (
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        http.post(identity.revealUrl);
                    }}
                    noValidate
                    className="mt-5 space-y-4"
                >
                    <Field label={t('manage.cases.identity.reason')} help={t('manage.cases.identity.reason_help')} error={http.errors.reason}>
                        {(field) => <Textarea {...field} name="reason" rows={2} minLength={10} maxLength={190} value={http.data.reason} onChange={(event) => http.setData('reason', event.target.value)} />}
                    </Field>
                    <div className="flex gap-2">
                        <Button type="submit" variant="secondary" disabled={http.processing}>
                            {t('manage.cases.identity.confirm')}
                        </Button>
                        <Button variant="quiet" onClick={() => setOpen(false)}>
                            {t('manage.cases.identity.cancel')}
                        </Button>
                    </div>
                </form>
            )}
        </section>
    );
}

function ReplyForm({ url }) {
    const t = useTranslator();
    const form = useForm({ body: '' });

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                form.post(url, { preserveScroll: true, onSuccess: () => form.reset() });
            }}
            noValidate
            className="mt-4 space-y-3 rounded-[24px] border border-line bg-surface p-5"
        >
            <Field label={t('manage.cases.reply.compose')} help={t('manage.cases.reply.help')} error={form.errors.body}>
                {(field) => <Textarea {...field} name="body" rows={4} maxLength={5000} value={form.data.body} onChange={(event) => form.setData('body', event.target.value)} />}
            </Field>
            <Button type="submit" disabled={form.processing}>
                <PaperPlaneRight size={20} aria-hidden="true" />
                {t('manage.cases.reply.submit')}
            </Button>
        </form>
    );
}

function NoteForm({ url }) {
    const t = useTranslator();
    const form = useForm({ note: '' });

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                form.post(url, { preserveScroll: true, onSuccess: () => form.reset() });
            }}
            noValidate
            className="mt-4 space-y-3"
        >
            <Field label={t('manage.cases.notes.compose')} error={form.errors.note}>
                {(field) => <Textarea {...field} name="note" rows={3} maxLength={5000} value={form.data.note} onChange={(event) => form.setData('note', event.target.value)} />}
            </Field>
            <Button type="submit" variant="secondary" disabled={form.processing}>
                <NotePencil size={20} aria-hidden="true" />
                {t('manage.cases.notes.submit')}
            </Button>
        </form>
    );
}

function AssignForm({ url, users, current }) {
    const t = useTranslator();
    const form = useForm({ assignee: current ?? '' });

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                form.post(url, { preserveScroll: true });
            }}
            noValidate
            className="space-y-3 rounded-[24px] border border-line bg-surface p-5"
        >
            <Field label={t('manage.cases.actions.assignment.label')} error={form.errors.assignee}>
                {(field) => (
                    <Select {...field} name="assignee" value={form.data.assignee} onChange={(event) => form.setData('assignee', event.target.value)}>
                        <option value="">{t('manage.cases.actions.assignment.none')}</option>
                        {users.map((user) => (
                            <option key={user.value} value={user.value}>
                                {user.label}
                            </option>
                        ))}
                    </Select>
                )}
            </Field>
            <Button type="submit" variant="secondary" size="sm" className="w-full" disabled={form.processing}>
                {t('manage.cases.actions.assignment.submit')}
            </Button>
        </form>
    );
}

function StatusForm({ url, transitions }) {
    const t = useTranslator();
    const form = useForm({ status: '', comment: '' });

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                form.post(url, { preserveScroll: true, onSuccess: () => form.reset() });
            }}
            noValidate
            className="space-y-3 rounded-[24px] border border-line bg-surface p-5"
        >
            {transitions.length === 0 ? (
                <>
                    <p className="font-semibold">{t('manage.cases.actions.status.label')}</p>
                    <p className="text-[15px] text-muted">{t('manage.cases.actions.status.none')}</p>
                </>
            ) : (
                <>
                    <Field label={t('manage.cases.actions.status.label')} error={form.errors.status}>
                        {(field) => (
                            <Select {...field} name="status" value={form.data.status} onChange={(event) => form.setData('status', event.target.value)}>
                                <option value="">{t('manage.cases.actions.status.choose')}</option>
                                {transitions.map((transition) => (
                                    <option key={transition.value} value={transition.value}>
                                        {transition.label}
                                    </option>
                                ))}
                            </Select>
                        )}
                    </Field>
                    <Field label={t('manage.cases.actions.status.comment')} help={t('manage.cases.actions.status.comment_help')} error={form.errors.comment}>
                        {(field) => <Textarea {...field} name="comment" rows={2} maxLength={2000} value={form.data.comment} onChange={(event) => form.setData('comment', event.target.value)} />}
                    </Field>
                    <Button type="submit" variant="secondary" size="sm" className="w-full" disabled={form.processing}>
                        {t('manage.cases.actions.status.submit')}
                    </Button>
                </>
            )}
        </form>
    );
}

function PriorityForm({ url, priorities, current }) {
    const t = useTranslator();
    const form = useForm({ priority: current });

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                form.post(url, { preserveScroll: true });
            }}
            noValidate
            className="space-y-3 rounded-[24px] border border-line bg-surface p-5"
        >
            <Field label={t('manage.cases.actions.priority.label')} error={form.errors.priority}>
                {(field) => (
                    <Select {...field} name="priority" value={form.data.priority} onChange={(event) => form.setData('priority', event.target.value)}>
                        {priorities.map((priority) => (
                            <option key={priority.value} value={priority.value}>
                                {priority.label}
                            </option>
                        ))}
                    </Select>
                )}
            </Field>
            <Button type="submit" variant="secondary" size="sm" className="w-full" disabled={form.processing}>
                {t('manage.cases.actions.priority.submit')}
            </Button>
        </form>
    );
}

Show.layout = (page) => <ManageLayout>{page}</ManageLayout>;
