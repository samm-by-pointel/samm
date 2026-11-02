import { router, useForm, usePage } from '@inertiajs/react';
import { DoorOpen, PaperPlaneRight } from '@phosphor-icons/react';
import { MessageThread, PageTitle } from '../../components/samm';
import { Alert, Button, Field, StatusBadge, Textarea } from '../../components/ui';
import PublicLayout, { Narrow } from '../../layouts/PublicLayout';
import { useDateFormatter, useTranslator } from '../../lib/i18n';

export default function Show({ report, messages, messageAction, logoutAction }) {
    const { status, site } = usePage().props;
    const t = useTranslator();
    const formatDate = useDateFormatter();
    const form = useForm({ body: '' });

    const send = (event) => {
        event.preventDefault();
        form.post(messageAction, { preserveScroll: true, onSuccess: () => form.reset() });
    };

    return (
        <Narrow className="max-w-[800px]">
            <PageTitle title={t('tracking.show.title')} />

            <div className="flex flex-wrap items-start justify-between gap-4">
                <h1 className="text-[clamp(32px,5vw,44px)] leading-tight font-bold">{t('tracking.show.title')}</h1>
                <StatusBadge status={report.status} />
            </div>

            <dl className="mt-6 grid gap-x-8 gap-y-4 rounded-3xl bg-surface-alt p-6 sm:grid-cols-3">
                <div>
                    <dt className="text-[14px] font-semibold text-muted">{t('tracking.show.reference')}</dt>
                    <dd className="mt-0.5 font-mono font-bold tracking-wider">{report.reference}</dd>
                </div>
                <div>
                    <dt className="text-[14px] font-semibold text-muted">{t('tracking.show.category')}</dt>
                    <dd className="mt-0.5 font-semibold">{report.category}</dd>
                </div>
                <div>
                    <dt className="text-[14px] font-semibold text-muted">{t('tracking.show.submitted_at')}</dt>
                    <dd className="mt-0.5 font-semibold">
                        <time dateTime={report.submittedAt}>{formatDate(report.submittedAt)}</time>
                    </dd>
                </div>
            </dl>

            {status && (
                <Alert tone="succes" className="mt-6">
                    {status}
                </Alert>
            )}

            <section aria-labelledby="thread-title" className="mt-12">
                <h2 id="thread-title" className="text-[24px] font-bold">
                    {t('tracking.thread.title')}
                </h2>
                <p className="mt-2 mb-6 text-muted">{t('tracking.thread.lead')}</p>
                <MessageThread messages={messages} />

                <form onSubmit={send} noValidate className="mt-6 space-y-4 rounded-3xl border border-line p-5 sm:p-6">
                    <Field label={t('tracking.thread.compose')} help={t('tracking.thread.compose_help')} error={form.errors.body}>
                        {(field) => <Textarea {...field} name="body" rows={5} maxLength={5000} value={form.data.body} onChange={(event) => form.setData('body', event.target.value)} />}
                    </Field>
                    <Button type="submit" disabled={form.processing}>
                        <PaperPlaneRight size={20} aria-hidden="true" />
                        {t('tracking.thread.send')}
                    </Button>
                </form>
            </section>

            <section className="mt-12 border-t border-line pt-8">
                <Button variant="secondary" onClick={() => router.post(logoutAction)}>
                    <DoorOpen size={20} aria-hidden="true" />
                    {t('tracking.show.logout')}
                </Button>
                <p className="mt-3 text-[15px] text-muted">{t('tracking.show.logout_help', { minutes: site.trackingSessionMinutes })}</p>
            </section>
        </Narrow>
    );
}

Show.layout = (page) => <PublicLayout>{page}</PublicLayout>;
