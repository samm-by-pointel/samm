import { router, usePage } from '@inertiajs/react';
import { ArrowRight, Check, Clock, X } from '../../components/icons';
import { IllustrationDescribe } from '../../components/illustrations';
import { PageTitle, PublicHero } from '../../components/samm';
import { Alert, Button } from '../../components/ui';
import PublicLayout, { Narrow } from '../../layouts/PublicLayout';
import { useTranslator } from '../../lib/i18n';

export default function Start({ hasDraft, beginUrl, resumeUrl, abandonUrl }) {
    const { routes } = usePage().props;
    const t = useTranslator();

    return (
        <>
            <PageTitle title={t('report_form.start.title')} />
            <PublicHero title={t('report_form.start.title')} lead={t('report_form.start.lead')} aside={<IllustrationDescribe className="w-60" />} />

            <Narrow className="-mt-8 pt-0 sm:pt-0">
                <div className="grid gap-4 sm:grid-cols-2">
                    <section className="rounded-[28px] border border-line bg-surface p-6">
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
                    <section className="rounded-[28px] border border-line bg-surface-alt p-6">
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
                                <ArrowRight size={18} weight="bold" aria-hidden="true" />
                            </Button>
                            <Button variant="quiet" onClick={() => router.post(abandonUrl)}>
                                {t('report_form.abandon')}
                            </Button>
                        </>
                    ) : (
                        <Button href={beginUrl} size="lg">
                            {t('report_form.start.begin')}
                            <ArrowRight size={18} weight="bold" aria-hidden="true" />
                        </Button>
                    )}
                    <Button href={routes.home} variant="quiet">
                        {t('report_form.back_home')}
                    </Button>
                </div>
            </Narrow>
        </>
    );
}

Start.layout = (page) => <PublicLayout>{page}</PublicLayout>;
