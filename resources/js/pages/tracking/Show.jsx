import { router, useForm, usePage } from '@inertiajs/react';
import { DoorOpen, PaperPlaneRight } from '../../components/icons';
import { IllustrationFollow } from '../../components/illustrations';
import { MessageThread, PageTitle, PublicHero } from '../../components/samm';
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

    const tiles = [
        { term: t('tracking.show.reference'), detail: <span className="font-mono tracking-wider">{report.reference}</span> },
        { term: t('tracking.show.category'), detail: report.category },
        { term: t('tracking.show.submitted_at'), detail: <time dateTime={report.submittedAt}>{formatDate(report.submittedAt)}</time> },
    ];

    return (
        <>
            <PageTitle title={t('tracking.show.title')} />
            <PublicHero title={t('tracking.show.title')} aside={<IllustrationFollow className="w-60" />}>
                <div className="mt-4 flex flex-wrap items-center gap-3">
                    <span className="text-[15px] font-semibold text-muted">{t('tracking.show.status')}</span>
                    <StatusBadge status={report.status} />
                </div>
            </PublicHero>

            <Narrow className="-mt-8 pt-0 sm:pt-0">
                <dl className="grid gap-4 sm:grid-cols-3">
                    {tiles.map(({ term, detail }) => (
                        <div key={term} className="rounded-2xl border border-line bg-surface p-5">
                            <dt className="text-[14px] font-semibold text-muted">{term}</dt>
                            <dd className="mt-1 font-bold">{detail}</dd>
                        </div>
                    ))}
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
                    <span aria-hidden="true" className="mt-3 block h-1 w-12 rounded-full bg-brand" />
                    <p className="mt-4 mb-6 text-muted">{t('tracking.thread.lead')}</p>
                    <MessageThread messages={messages} />

                    <form onSubmit={send} noValidate className="mt-6 overflow-hidden rounded-[28px] border border-line bg-surface">
                        <div className="p-6">
                            <Field label={t('tracking.thread.compose')} help={t('tracking.thread.compose_help')} error={form.errors.body}>
                                {(field) => <Textarea {...field} name="body" rows={5} maxLength={5000} value={form.data.body} onChange={(event) => form.setData('body', event.target.value)} />}
                            </Field>
                        </div>
                        <div className="border-t border-line bg-surface-alt px-6 py-4">
                            <Button type="submit" disabled={form.processing}>
                                <PaperPlaneRight size={20} aria-hidden="true" />
                                {t('tracking.thread.send')}
                            </Button>
                        </div>
                    </form>
                </section>

                <section className="mt-12 border-t border-line pt-8">
                    <Button variant="quiet" onClick={() => router.post(logoutAction)}>
                        <DoorOpen size={20} aria-hidden="true" />
                        {t('tracking.show.logout')}
                    </Button>
                    <p className="mt-3 text-[15px] text-muted">{t('tracking.show.logout_help', { minutes: site.trackingSessionMinutes })}</p>
                </section>
            </Narrow>
        </>
    );
}

Show.layout = (page) => <PublicLayout>{page}</PublicLayout>;
