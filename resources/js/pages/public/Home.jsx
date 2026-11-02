import { usePage } from '@inertiajs/react';
import { Check, EyeSlash, LockKey, UserCircleDashed } from '@phosphor-icons/react';
import { CodeTicket, EmergencyContacts, PageTitle, SammSymbol } from '../../components/samm';
import { Alert, Button } from '../../components/ui';
import PublicLayout from '../../layouts/PublicLayout';
import { useTranslator } from '../../lib/i18n';

const LEVEL_ICONS = { anonymous: EyeSlash, pseudonymous: UserCircleDashed, confidential: LockKey };

export default function Home({ levels, images }) {
    const { routes, status } = usePage().props;
    const t = useTranslator();

    return (
        <>
            <PageTitle />

            {/* Accroche : ce qu'on peut faire ici, et le code qui permet d'y revenir */}
            <section className="overflow-hidden">
                <div className="mx-auto grid max-w-6xl items-center gap-12 px-5 pt-12 pb-16 sm:px-8 lg:grid-cols-[1.15fr_1fr] lg:pt-20 lg:pb-24">
                    <div>
                        <h1 className="text-[clamp(38px,6vw,62px)] leading-[1.04] font-bold tracking-[-0.02em]">{t('public.home.title')}</h1>
                        <p className="mt-6 max-w-xl text-[19px] leading-relaxed text-muted">{t('public.home.lead')}</p>

                        {status && <Alert className="mt-6">{status}</Alert>}

                        <div className="mt-9 flex flex-wrap gap-3">
                            <Button href={routes.reportStart} size="lg">
                                {t('public.home.report_cta')}
                            </Button>
                            <Button href={routes.trackingLogin} size="lg" variant="secondary">
                                {t('public.home.track_cta')}
                            </Button>
                        </div>

                        <ul className="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-[15px] text-muted">
                            {['point_account', 'point_anonymous', 'point_code'].map((key) => (
                                <li key={key} className="flex items-center gap-2">
                                    <Check size={18} weight="bold" className="text-succes" aria-hidden="true" />
                                    {t(`public.home.${key}`)}
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div>
                        {images.hero ? (
                            <img src={images.hero} alt="" width="1600" height="1067" fetchPriority="high" className="aspect-[4/3] w-full rounded-[36px] object-cover object-[50%_35%]" />
                        ) : (
                            <div aria-hidden="true" className="relative grid aspect-[4/4.4] w-full place-items-center overflow-hidden rounded-[36px] bg-brand-soft">
                                <SammSymbol size={360} className="absolute -right-16 -bottom-20 text-brand opacity-[0.09]" />
                                <SammSymbol size={88} className="text-brand" />
                            </div>
                        )}
                        <div className="relative z-10 -mt-14 ml-4 w-[min(88%,360px)] sm:-ml-6">
                            <CodeTicket
                                className="shadow-[0_18px_40px_-12px_rgb(28_26_51/0.35)]"
                                notchClass={null}
                                rows={[{ label: t('public.home.ticket_label'), value: 'SMM-7KQ4-M2XD' }]}
                                caption={t('public.home.ticket_caption')}
                            />
                        </div>
                    </div>
                </div>
            </section>

            <EmergencyContacts variant="band" />

            {/* Une vraie séquence : trois étapes numérotées */}
            <section id="fonctionnement" aria-labelledby="fonctionnement-titre" className="scroll-mt-20">
                <div className="mx-auto max-w-6xl px-5 py-20 sm:px-8 lg:py-28">
                    <h2 id="fonctionnement-titre" className="max-w-2xl text-[clamp(28px,4vw,40px)] leading-tight font-bold">
                        {t('public.home.how_title')}
                    </h2>
                    <ol className="mt-12 grid gap-10 md:grid-cols-3 md:gap-8">
                        {['describe', 'code', 'follow'].map((step, index) => (
                            <li key={step} className="relative">
                                <span aria-hidden="true" className="grid size-12 place-items-center rounded-full bg-brand text-[20px] font-bold text-on-brand">
                                    {index + 1}
                                </span>
                                {index < 2 && <span aria-hidden="true" className="absolute top-6 left-16 hidden h-px w-[calc(100%-4rem)] bg-line-strong md:block" />}
                                <h3 className="mt-5 text-[21px] font-bold">{t(`public.home.how.${step}.title`)}</h3>
                                <p className="mt-2 text-muted">{t(`public.home.how.${step}.text`)}</p>
                            </li>
                        ))}
                    </ol>
                </div>
            </section>

            <section id="garanties" aria-labelledby="garanties-titre" className="scroll-mt-20 bg-surface-alt">
                <div className="mx-auto max-w-6xl px-5 py-20 sm:px-8 lg:py-28">
                    <div className="max-w-2xl">
                        <h2 id="garanties-titre" className="text-[clamp(28px,4vw,40px)] leading-tight font-bold">
                            {t('public.home.guarantees_title')}
                        </h2>
                        <p className="mt-4 text-[19px] text-muted">{t('public.home.guarantees_lead')}</p>
                    </div>

                    <div className="mt-12 grid gap-4 md:grid-cols-3">
                        {levels.map((level) => {
                            const Icon = LEVEL_ICONS[level.value] ?? LockKey;

                            return (
                                <div key={level.value} className="rounded-3xl border border-line bg-surface p-7">
                                    <Icon size={34} className="text-brand" aria-hidden="true" />
                                    <h3 className="mt-5 text-[21px] font-bold">{level.label}</h3>
                                    <p className="mt-2 text-muted">{level.description}</p>
                                </div>
                            );
                        })}
                    </div>

                    <ul className="mt-12 grid gap-x-10 gap-y-5 md:grid-cols-2">
                        {['ip', 'encryption', 'access', 'human'].map((key) => (
                            <li key={key} className="flex gap-3">
                                <Check size={22} weight="bold" className="mt-0.5 shrink-0 text-succes" aria-hidden="true" />
                                <span>{t(`public.home.principles.${key}`)}</span>
                            </li>
                        ))}
                    </ul>
                </div>
            </section>

            <section aria-labelledby="destinataires-titre">
                <div className="mx-auto grid max-w-6xl items-center gap-12 px-5 py-20 sm:px-8 lg:grid-cols-2 lg:py-28">
                    {images.team && <img src={images.team} alt="" width="1600" height="1068" loading="lazy" className="aspect-[3/2] w-full rounded-[36px] object-cover" />}
                    <div className={images.team ? undefined : 'max-w-2xl'}>
                        <h2 id="destinataires-titre" className="text-[clamp(28px,4vw,40px)] leading-tight font-bold">
                            {t('public.home.who_title')}
                        </h2>
                        <p className="mt-5 text-[19px] leading-relaxed">{t('public.home.who_text')}</p>
                        <p className="mt-4 text-[19px] leading-relaxed text-muted">{t('public.home.who_text_2')}</p>
                    </div>
                </div>
            </section>

            <section id="questions" aria-labelledby="questions-titre" className="scroll-mt-20 bg-surface-alt">
                <div className="mx-auto max-w-3xl px-5 py-20 sm:px-8 lg:py-28">
                    <h2 id="questions-titre" className="text-[clamp(28px,4vw,40px)] leading-tight font-bold">
                        {t('public.home.faq_title')}
                    </h2>
                    <div className="mt-10 divide-y divide-line-strong/50 border-y border-line-strong/50">
                        {['anonymous', 'after', 'lost_code', 'quick_exit', 'history'].map((key) => (
                            <details key={key} className="group">
                                <summary className="flex min-h-16 cursor-pointer list-none items-center justify-between gap-6 py-4 text-[18px] font-semibold [&::-webkit-details-marker]:hidden">
                                    {t(`public.home.faq.${key}.q`)}
                                    <span aria-hidden="true" className="grid size-9 shrink-0 place-items-center rounded-full bg-surface text-[22px] text-brand transition-transform group-open:rotate-45">
                                        +
                                    </span>
                                </summary>
                                <p className="pb-6 text-muted">{t(`public.home.faq.${key}.a`)}</p>
                            </details>
                        ))}
                    </div>
                </div>
            </section>

            <section className="bg-brand text-on-brand">
                <div className="mx-auto flex max-w-6xl flex-col items-start gap-6 px-5 py-16 sm:px-8 md:flex-row md:items-center md:justify-between">
                    <p className="max-w-xl text-[26px] leading-snug font-bold">{t('public.home.cta_text')}</p>
                    <Button href={routes.reportStart} size="lg" variant="light">
                        {t('public.home.report_cta')}
                    </Button>
                </div>
            </section>
        </>
    );
}

Home.layout = (page) => <PublicLayout>{page}</PublicLayout>;
