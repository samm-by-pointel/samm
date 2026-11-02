import { router, usePage } from '@inertiajs/react';
import { Check, Clock, X } from '@phosphor-icons/react';
import { PageTitle } from '../../components/samm';
import { Alert, Button } from '../../components/ui';
import PublicLayout, { Narrow } from '../../layouts/PublicLayout';
import { useTranslator } from '../../lib/i18n';

export default function Start({ hasDraft, beginUrl, resumeUrl, abandonUrl }) {
    const { routes } = usePage().props;
    const t = useTranslator();

    return (
        <Narrow>
            <PageTitle title={t('report_form.start.title')} />

            <h1 className="text-[clamp(32px,5vw,44px)] leading-tight font-bold">{t('report_form.start.title')}</h1>
            <p className="mt-4 text-[19px] leading-relaxed text-muted">{t('report_form.start.lead')}</p>

            <div className="mt-10 grid gap-4 sm:grid-cols-2">
                <section className="rounded-3xl border border-line p-6">
                    <h2 className="text-[19px] font-bold">{t('report_form.start.collected_title')}</h2>
                    <ul className="mt-4 space-y-3">
                        {[1, 2, 3].map((n) => (
                            <li key={n} className="flex gap-3">
                                <Check size={20} weight="bold" className="mt-1 shrink-0 text-brand" aria-hidden="true" />
                                <span>{t(`report_form.start.collected_${n}`)}</span>
                            </li>
                        ))}
                    </ul>
                </section>
                <section className="rounded-3xl bg-surface-alt p-6">
                    <h2 className="text-[19px] font-bold">{t('report_form.start.not_collected_title')}</h2>
                    <ul className="mt-4 space-y-3">
                        {[1, 2].map((n) => (
                            <li key={n} className="flex gap-3">
                                <X size={20} weight="bold" className="mt-1 shrink-0 text-muted" aria-hidden="true" />
                                <span>{t(`report_form.start.not_collected_${n}`)}</span>
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
            <p className="mt-4 text-[15px] text-muted">{t('report_form.start.limits')}</p>

            <Alert className="mt-8" icon={Clock} title={t('report_form.start.session_title')}>
                {t('report_form.start.session_help')}
            </Alert>

            <div className="mt-10 flex flex-wrap items-center gap-3">
                {hasDraft ? (
                    <>
                        <Button href={resumeUrl} size="lg">
                            {t('report_form.start.resume')}
                        </Button>
                        <Button variant="quiet" onClick={() => router.post(abandonUrl)}>
                            {t('report_form.abandon')}
                        </Button>
                    </>
                ) : (
                    <Button href={beginUrl} size="lg">
                        {t('report_form.start.begin')}
                    </Button>
                )}
                <Button href={routes.home} variant="quiet">
                    {t('report_form.back_home')}
                </Button>
            </div>
        </Narrow>
    );
}

Start.layout = (page) => <PublicLayout>{page}</PublicLayout>;
