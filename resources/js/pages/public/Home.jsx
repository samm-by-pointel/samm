import { usePage } from '@inertiajs/react';
import { ArrowRight, Detective, FileText, LockKey, MagnifyingGlass, PencilSimple, ShieldCheck, User, UsersThree } from '../../components/icons';
import { Fragment } from 'react';
import { IllustrationCode, IllustrationDescribe, IllustrationFollow, Leaves, Wave } from '../../components/illustrations';
import { EmergencyContacts, PageTitle, SammSymbol } from '../../components/samm';
import { Alert, Button } from '../../components/ui';
import PublicLayout from '../../layouts/PublicLayout';
import { useTranslator } from '../../lib/i18n';

/* Même couleur et même taille pour les trois niveaux : aucun n'est mis en avant (charte §12.4) */
const LEVEL_ICONS = { anonymous: Detective, pseudonymous: User, confidential: LockKey };

const STEPS = [
    { key: 'describe', Illustration: IllustrationDescribe },
    { key: 'code', Illustration: IllustrationCode },
    { key: 'follow', Illustration: IllustrationFollow },
];

const PRINCIPLES = [
    { key: 'ip', Icon: ShieldCheck },
    { key: 'encryption', Icon: LockKey },
    { key: 'access', Icon: FileText },
    { key: 'human', Icon: UsersThree },
];

const FAQ_COLUMNS = [
    ['anonymous', 'after'],
    ['lost_code', 'quick_exit', 'history'],
];

function SectionTitle({ id, children, centered = false }) {
    return (
        <div className={centered ? 'text-center' : undefined}>
            <h2 id={id} className="text-[clamp(28px,4vw,38px)] leading-tight font-bold">
                {children}
            </h2>
            <span aria-hidden="true" className={`mt-3 block h-1 w-12 rounded-full bg-brand ${centered ? 'mx-auto' : ''}`} />
        </div>
    );
}

export default function Home({ levels, images }) {
    const { routes, status, site } = usePage().props;
    const t = useTranslator();

    const actions = (
        <div className="flex flex-wrap gap-3">
            <Button href={routes.reportStart} size="lg">
                <PencilSimple size={20} weight="bold" aria-hidden="true" />
                {t('public.home.report_cta')}
                <ArrowRight size={18} weight="bold" aria-hidden="true" />
            </Button>
            <Button href={routes.trackingLogin} size="lg" variant="secondary" className="border-brand text-brand">
                <MagnifyingGlass size={20} weight="bold" aria-hidden="true" />
                {t('public.home.track_cta')}
            </Button>
        </div>
    );

    return (
        <>
            <PageTitle />

            {/* Accroche : ce qu'on peut faire ici, la photo à droite, une vague pour finir */}
            <section className="relative overflow-hidden bg-surface-alt">
                {images.hero && (
                    <div className="absolute inset-y-0 right-0 hidden w-[46%] lg:block">
                        <img src={images.hero} alt="" width="1600" height="1067" fetchPriority="high" className="h-full w-full rounded-bl-[140px] object-cover object-[50%_30%]" />
                    </div>
                )}
                <Leaves className="absolute top-16 right-4 hidden w-28 lg:block xl:right-10 xl:w-36" />

                <div className="relative mx-auto max-w-7xl px-5 pt-14 pb-28 sm:px-8 lg:pt-16 lg:pb-32">
                    <div className="max-w-xl lg:max-w-[50%]">
                        <h1 className="text-[clamp(38px,4.6vw,56px)] leading-[1.05] font-bold tracking-[-0.02em]">
                            {t('public.home.title_lead')} <span className="text-brand">{t('public.home.title_highlight')}</span>
                        </h1>
                        <p className="mt-6 text-[19px] leading-relaxed font-semibold">{t('public.home.lead')}</p>

                        {status && <Alert className="mt-6">{status}</Alert>}

                        <div className="mt-9">{actions}</div>

                        <ul className="mt-10 grid gap-4 sm:grid-cols-3 sm:gap-0 sm:divide-x sm:divide-line-strong/40">
                            {[
                                ['point_account', LockKey],
                                ['point_anonymous', Detective],
                                ['point_code', ShieldCheck],
                            ].map(([key, Icon]) => (
                                <li key={key} className="flex items-center gap-3 text-[15px] font-semibold sm:px-4 sm:first:pl-0">
                                    <Icon size={28} weight="fill" className="shrink-0 text-brand" aria-hidden="true" />
                                    {t(`public.home.${key}`)}
                                </li>
                            ))}
                        </ul>
                    </div>

                    {images.hero && <img src={images.hero} alt="" width="1600" height="1067" className="mt-10 aspect-[4/3] w-full rounded-[32px] object-cover lg:hidden" />}
                </div>

                <Wave className="absolute bottom-0 left-0 h-14 w-full lg:h-24" />
            </section>

            <div className="mx-auto max-w-7xl px-5 sm:px-8">
                <EmergencyContacts />
            </div>

            {/* Une vraie séquence : trois étapes numérotées et reliées */}
            <section id="fonctionnement" aria-labelledby="fonctionnement-titre" className="scroll-mt-24">
                <div className="mx-auto max-w-7xl px-5 py-20 sm:px-8 lg:py-24">
                    <SectionTitle id="fonctionnement-titre" centered>
                        {t('public.home.how_title')}
                    </SectionTitle>
                    <ol className="mt-12 grid gap-12 md:grid-cols-[1fr_auto_1fr_auto_1fr] md:items-start md:gap-4">
                        {STEPS.map(({ key, Illustration }, index) => (
                            <Fragment key={key}>
                                <li className="text-center">
                                    <Illustration className="mx-auto h-36 w-auto" />
                                    <h3 className="mt-4 flex items-center justify-center gap-2.5 text-[21px] font-bold">
                                        <span aria-hidden="true" className="grid size-8 shrink-0 place-items-center rounded-full bg-brand text-[16px] text-on-brand">
                                            {index + 1}
                                        </span>
                                        {t(`public.home.how.${key}.title`)}
                                    </h3>
                                    <p className="mx-auto mt-3 max-w-xs text-[16px] text-muted">{t(`public.home.how.${key}.text`)}</p>
                                </li>
                                {index < STEPS.length - 1 && (
                                    <li aria-hidden="true" className="hidden pt-16 md:block">
                                        <ArrowRight size={32} className="text-line-strong" />
                                    </li>
                                )}
                            </Fragment>
                        ))}
                    </ol>
                </div>
            </section>

            {/* Les trois niveaux, puis ce qui est garanti quel que soit le choix */}
            <section id="garanties" aria-labelledby="garanties-titre" className="scroll-mt-24">
                <div className="mx-auto grid max-w-7xl gap-6 px-5 pb-20 sm:px-8 lg:pb-24 xl:grid-cols-[1.55fr_1fr]">
                    <div className="rounded-[28px] border border-line bg-surface-alt p-6 sm:p-8">
                        <SectionTitle id="garanties-titre">{t('public.home.guarantees_title')}</SectionTitle>
                        <p className="mt-4 font-semibold">{t('public.home.guarantees_lead')}</p>
                        <div className="mt-7 grid gap-4 md:grid-cols-3">
                            {levels.map((level) => {
                                const Icon = LEVEL_ICONS[level.value] ?? LockKey;

                                return (
                                    <div key={level.value} className="rounded-2xl border border-line bg-surface p-5 text-center">
                                        <Icon size={38} weight="fill" className="mx-auto text-brand" aria-hidden="true" />
                                        <h3 className="mt-3 text-[19px] font-bold">{level.label}</h3>
                                        <p className="mt-2 text-[15px] leading-snug text-muted">{level.description}</p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    <ul className="grid content-center gap-7 rounded-[28px] border border-line bg-surface p-6 sm:p-8 lg:grid-cols-2 xl:grid-cols-1">
                        {PRINCIPLES.map(({ key, Icon }) => (
                            <li key={key} className="flex items-start gap-4">
                                <Icon size={32} weight="fill" className="shrink-0 text-brand" aria-hidden="true" />
                                <span>{t(`public.home.principles.${key}`)}</span>
                            </li>
                        ))}
                    </ul>
                </div>
            </section>

            <section aria-labelledby="destinataires-titre" className="bg-surface-alt">
                <div className="grid items-center gap-10 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1fr)]">
                    {images.team && <img src={images.team} alt="" width="1600" height="1068" loading="lazy" className="aspect-[3/2] h-full w-full object-cover lg:rounded-r-[48px]" />}
                    <div className={`px-5 pb-14 sm:px-8 ${images.team ? 'lg:py-14 lg:pr-16' : 'mx-auto max-w-3xl py-16'}`}>
                        <SectionTitle id="destinataires-titre">{t('public.home.who_title')}</SectionTitle>
                        <p className="mt-6 text-[18px] leading-relaxed">{t('public.home.who_text')}</p>
                        <p className="mt-4 text-[18px] leading-relaxed">{t('public.home.who_text_2')}</p>
                    </div>
                </div>
            </section>

            <section id="questions" aria-labelledby="questions-titre" className="scroll-mt-24">
                <div className="mx-auto max-w-7xl px-5 py-20 sm:px-8 lg:py-24">
                    <SectionTitle id="questions-titre" centered>
                        {t('public.home.faq_title')}
                    </SectionTitle>
                    <div className="mt-10 grid items-start gap-4 lg:grid-cols-2">
                        {FAQ_COLUMNS.map((column) => (
                            <div key={column[0]} className="divide-y divide-line rounded-2xl border border-line bg-surface px-5">
                                {column.map((key) => (
                                    <details key={key} className="group">
                                        <summary className="flex min-h-14 cursor-pointer list-none items-center justify-between gap-6 py-3 font-semibold [&::-webkit-details-marker]:hidden">
                                            {t(`public.home.faq.${key}.q`)}
                                            <span aria-hidden="true" className="shrink-0 text-[24px] leading-none text-ink transition-transform group-open:rotate-45">
                                                +
                                            </span>
                                        </summary>
                                        <p className="pb-5 text-muted">{t(`public.home.faq.${key}.a`)}</p>
                                    </details>
                                ))}
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Appel final : la bulle protégée du symbole, pas un mégaphone (charte §2) */}
            <section className="mx-auto w-full max-w-7xl px-5 pb-16 sm:px-8">
                <div className="flex flex-col items-center gap-8 rounded-[28px] bg-brand-soft px-6 py-10 text-center md:flex-row md:text-left lg:px-12">
                    <span aria-hidden="true" className="grid size-20 shrink-0 place-items-center rounded-full bg-surface">
                        <SammSymbol size={48} className="text-logo" />
                    </span>
                    <div className="flex-1">
                        <p className="text-[24px] leading-snug font-bold text-brand">{t('public.home.cta_text')}</p>
                        <div className="mt-5 flex justify-center md:justify-start">{actions}</div>
                    </div>
                    <span aria-hidden="true" className="hidden text-[40px] font-bold text-brand lg:block">
                        {site.appName}
                    </span>
                </div>
            </section>
        </>
    );
}

Home.layout = (page) => <PublicLayout>{page}</PublicLayout>;
