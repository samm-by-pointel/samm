import { router } from '@inertiajs/react';
import { ArrowRight, Check, Clock, X } from '../../components/icons';
import { PageTitle } from '../../components/samm';
import { Button } from '../../components/ui';
import PublicLayout from '../../layouts/PublicLayout';
import { useTranslator } from '../../lib/i18n';

/* Les six étapes du formulaire, dans l'ordre du Stepper (cahier des charges §6) */
const STEPS = ['situation', 'confidentialite', 'description', 'personnes', 'pieces-jointes', 'consentement'];

export default function Start({ hasDraft, beginUrl, resumeUrl, abandonUrl }) {
    const t = useTranslator();
    const startUrl = hasDraft ? resumeUrl : beginUrl;
    const startLabel = hasDraft ? t('report_form.start.resume') : t('report_form.start.begin');

    return (
        <>
            <PageTitle title={t('report_form.start.title')} />

            <div className="mx-auto max-w-6xl px-5 py-10 sm:px-8 sm:py-16">
                <header>
                    <h1 className="text-[clamp(30px,4.5vw,44px)] leading-tight font-bold tracking-[-0.01em]">{t('report_form.start.title')}</h1>
                    <p className="mt-4 max-w-3xl text-[18px] leading-relaxed">{t('report_form.start.lead')}</p>
                    <div className="mt-8 flex flex-wrap items-center gap-3">
                        <Button href={startUrl} size="lg">
                            {startLabel}
                            <ArrowRight size={18} weight="bold" aria-hidden="true" />
                        </Button>
                        {hasDraft && (
                            <Button variant="quiet" onClick={() => router.post(abandonUrl)}>
                                {t('report_form.abandon')}
                            </Button>
                        )}
                    </div>
                </header>

                <section aria-labelledby="etapes-titre" className="mt-16">
                    <h2 id="etapes-titre" className="text-[24px] font-bold">
                        {t('report_form.start.steps_title')}
                    </h2>
                    <ol className="mt-8 grid gap-x-8 gap-y-10 md:grid-cols-2 lg:grid-cols-3">
                        {STEPS.map((step, index) => (
                            <li key={step} className="flex gap-4">
                                <span className="grid size-11 shrink-0 place-items-center rounded-full bg-brand-soft text-[17px] font-bold text-brand">{index + 1}</span>
                                <div className="pt-2">
                                    <h3 className="text-[18px] font-semibold">{t(`report_form.steps.${step}`)}</h3>
                                    <p className="mt-1.5 text-muted">{t(`report_form.start.step_help.${step}`)}</p>
                                </div>
                            </li>
                        ))}
                    </ol>
                </section>

                <section className="mt-16 border-t border-line pt-12">
                    <div className="grid gap-10 md:grid-cols-2">
                        <div>
                            <h2 className="text-[20px] font-bold">{t('report_form.start.collected_title')}</h2>
                            <ul className="mt-5 space-y-3">
                                {[1, 2, 3].map((n) => (
                                    <li key={n} className="flex gap-3">
                                        <Check size={20} weight="bold" className="mt-0.5 shrink-0 text-brand" aria-hidden="true" />
                                        <span>{t(`report_form.start.collected_${n}`)}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <div>
                            <h2 className="text-[20px] font-bold">{t('report_form.start.not_collected_title')}</h2>
                            <ul className="mt-5 space-y-3">
                                {[1, 2].map((n) => (
                                    <li key={n} className="flex gap-3">
                                        <X size={20} weight="bold" className="mt-0.5 shrink-0 text-muted" aria-hidden="true" />
                                        <span>{t(`report_form.start.not_collected_${n}`)}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                    <p className="mt-8 max-w-3xl text-[15px] text-muted">{t('report_form.start.limits')}</p>
                </section>

                <p className="mt-12 flex max-w-3xl items-start gap-2 text-[15px] text-muted">
                    <Clock size={20} className="mt-0.5 shrink-0" aria-hidden="true" />
                    <span>
                        <strong className="font-semibold text-ink">{t('report_form.start.session_title')}.</strong> {t('report_form.start.session_help')}
                    </span>
                </p>

                <div className="mt-8">
                    <Button href={startUrl} variant="secondary" size="lg">
                        {startLabel}
                        <ArrowRight size={18} weight="bold" aria-hidden="true" />
                    </Button>
                </div>
            </div>
        </>
    );
}

Start.layout = (page) => <PublicLayout>{page}</PublicLayout>;
