import { router, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, EyeSlash, LockKey, Paperclip, UserCircleDashed } from '../../components/icons';
import { EmergencyContacts, PageTitle, PublicHero, Stepper } from '../../components/samm';
import { Alert, Button, Checkbox, Field, FieldError, Input, RadioCards, Textarea, cx } from '../../components/ui';
import PublicLayout, { Narrow } from '../../layouts/PublicLayout';
import { useTranslator } from '../../lib/i18n';

const LEVEL_ICONS = { anonymous: EyeSlash, pseudonymous: UserCircleDashed, confidential: LockKey };

const LEADS = {
    situation: 'report_form.situation.lead',
    confidentialite: 'report_form.confidentiality.lead',
    description: 'report_form.description.lead',
    personnes: 'report_form.people.lead',
    'pieces-jointes': 'report_form.attachments.lead',
    consentement: 'report_form.consent.lead',
};

/* Valeurs de départ de chaque étape, reprises du brouillon conservé en session */
function initialData(step, values) {
    const text = (name) => values[name] ?? '';
    const flag = (name) => (values[name] === true || values[name] === false ? values[name] : null);

    switch (step) {
        case 'situation':
            return { category: text('category') };
        case 'confidentialite':
            return {
                confidentiality_level: text('confidentiality_level'),
                pseudonym: text('pseudonym'),
                identity_full_name: text('identity_full_name'),
                identity_email: text('identity_email'),
                identity_phone: text('identity_phone'),
            };
        case 'description':
            return {
                description: text('description'),
                facts_date: text('facts_date'),
                facts_period: text('facts_period'),
                location: text('location'),
                is_recurring: flag('is_recurring'),
                is_ongoing: flag('is_ongoing'),
                immediate_danger: flag('immediate_danger'),
            };
        case 'personnes':
            return { people_involved: text('people_involved'), witnesses: text('witnesses') };
        case 'consentement':
            return {
                consent_processing: Boolean(values.consent_processing),
                consent_contact: Boolean(values.consent_contact),
                consent_identity_disclosure: Boolean(values.consent_identity_disclosure),
            };
        default:
            return {};
    }
}

export default function Step(props) {
    // Une clé par étape : chaque écran repart de son propre brouillon.
    return <StepForm key={props.step} {...props} />;
}

function StepForm(props) {
    const { step, title, steps, values, action, backUrl, abandonUrl } = props;
    const t = useTranslator();
    const form = useForm(initialData(step, values));
    const hasErrors = Object.keys(form.errors).length > 0;

    const submitLabel = {
        'pieces-jointes': t('report_form.attachments.continue_without'),
        consentement: t('report_form.consent.submit'),
    }[step] ?? t('report_form.continue');

    const submit = (event) => {
        event.preventDefault();
        form.post(action, { preserveScroll: (page) => Object.keys(page.props.errors ?? {}).length > 0 });
    };

    return (
        <>
            <PageTitle title={title} />
            <PublicHero title={title} lead={t(LEADS[step])} eyebrow={<div className="mb-8"><Stepper steps={steps} current={step} /></div>} />

            <Narrow className="-mt-8 pt-0 sm:pt-0">
                <Intro {...props} />

                {hasErrors && (
                    <Alert tone="urgent" className="mt-6" title={t('report_form.errors.title')}>
                        {t('report_form.errors.help')}
                    </Alert>
                )}

                <form onSubmit={submit} noValidate className="mt-6 overflow-hidden rounded-[28px] border border-line bg-surface">
                    <div className="space-y-8 p-6 sm:p-8">
                        <Fields {...props} form={form} />
                    </div>

                    <div className="flex flex-wrap items-center gap-3 border-t border-line bg-surface-alt px-6 py-5 sm:px-8">
                        <Button type="submit" size="lg" disabled={form.processing}>
                            {submitLabel}
                            <ArrowRight size={18} weight="bold" aria-hidden="true" />
                        </Button>
                        <Button href={backUrl} variant="quiet">
                            <ArrowLeft size={18} aria-hidden="true" />
                            {t('report_form.back')}
                        </Button>
                    </div>
                </form>

                <div className="mt-6">
                    <button
                        type="button"
                        onClick={() => router.post(abandonUrl)}
                        className="min-h-11 rounded-xl px-1 text-[15px] font-semibold text-muted underline underline-offset-4 hover:text-ink"
                    >
                        {t('report_form.abandon')}
                    </button>
                </div>
            </Narrow>
        </>
    );
}

/* Phrase d'introduction de chaque étape, affichée dans le bandeau */
function Intro({ step, immediateDanger, summary }) {
    const t = useTranslator();

    return (
        <>

            {step === 'personnes' && immediateDanger && <EmergencyContacts />}

            {step === 'consentement' && (
                <section aria-labelledby="recap-title" className="rounded-[28px] border border-line bg-surface p-6">
                    <h2 id="recap-title" className="text-[19px] font-bold">
                        {t('report_form.consent.summary_title')}
                    </h2>
                    <dl className="mt-4 grid gap-4 sm:grid-cols-3">
                        {[
                            ['summary_category', summary.category ?? '-'],
                            ['summary_level', summary.level ?? '-'],
                            ['summary_danger', immediateDanger ? t('report_form.yes') : t('report_form.no')],
                        ].map(([key, detail]) => (
                            <div key={key}>
                                <dt className="text-[14px] font-semibold text-muted">{t(`report_form.consent.${key}`)}</dt>
                                <dd className="mt-0.5 font-semibold">{detail}</dd>
                            </div>
                        ))}
                    </dl>
                    <p className="mt-4 text-[15px] text-muted">{t('report_form.consent.summary_help')}</p>
                </section>
            )}
        </>
    );
}

function Fields({ step, form, categories, levels, isConfidential, today }) {
    const t = useTranslator();
    const { data, setData, errors } = form;
    const optional = t('report_form.optional');

    const text = (name, label, { help, type = 'text', maxLength, isOptional = true, ...rest } = {}) => (
        <Field label={label} optional={isOptional ? optional : undefined} help={help} error={errors[name]}>
            {(field) => (
                <Input {...field} {...rest} type={type} name={name} value={data[name]} maxLength={maxLength} autoComplete="off" onChange={(event) => setData(name, event.target.value)} />
            )}
        </Field>
    );

    const longText = (name, label, { help, rows = 4, isOptional = true, maxLength } = {}) => (
        <Field label={label} optional={isOptional ? optional : undefined} help={help} error={errors[name]}>
            {(field) => <Textarea {...field} name={name} rows={rows} maxLength={maxLength} value={data[name]} onChange={(event) => setData(name, event.target.value)} />}
        </Field>
    );

    switch (step) {
        case 'situation':
            return (
                <RadioCards
                    name="category"
                    legend={t('report_form.situation.legend')}
                    value={data.category}
                    onChange={(value) => setData('category', value)}
                    options={categories}
                    error={errors.category}
                    columns={2}
                />
            );

        case 'confidentialite':
            return (
                <>
                    <RadioCards
                        name="confidentiality_level"
                        legend={t('report_form.confidentiality.legend')}
                        value={data.confidentiality_level}
                        onChange={(value) => setData('confidentiality_level', value)}
                        options={levels.map((level) => ({ ...level, icon: LEVEL_ICONS[level.value] }))}
                        error={errors.confidentiality_level}
                    />

                    {data.confidentiality_level === 'pseudonymous' && (
                        <div className="space-y-4 rounded-2xl bg-surface-alt p-5">
                            <div>
                                <h2 className="font-bold">{t('report_form.confidentiality.pseudonym_title')}</h2>
                                <p className="mt-1 text-[15px] text-muted">{t('report_form.confidentiality.pseudonym_help')}</p>
                            </div>
                            {text('pseudonym', t('report_form.confidentiality.pseudonym'), { maxLength: 80, isOptional: false })}
                        </div>
                    )}

                    {data.confidentiality_level === 'confidential' && (
                        <div className="space-y-5 rounded-2xl bg-surface-alt p-5">
                            <div>
                                <h2 className="font-bold">{t('report_form.confidentiality.identity_title')}</h2>
                                <p className="mt-1 text-[15px] text-muted">{t('report_form.confidentiality.identity_help')}</p>
                            </div>
                            {text('identity_full_name', t('report_form.confidentiality.identity_full_name'), { maxLength: 190, isOptional: false })}
                            {text('identity_email', t('report_form.confidentiality.identity_email'), { type: 'email', maxLength: 190 })}
                            {text('identity_phone', t('report_form.confidentiality.identity_phone'), { type: 'tel', maxLength: 32 })}
                        </div>
                    )}
                </>
            );

        case 'description':
            return (
                <>
                    {longText('description', t('report_form.description.description'), {
                        help: t('report_form.description.description_help'),
                        rows: 9,
                        isOptional: false,
                        maxLength: 20000,
                    })}
                    <div className="grid gap-8 sm:grid-cols-2">
                        {text('facts_date', t('report_form.description.facts_date'), { type: 'date', max: today })}
                        {text('location', t('report_form.description.location'), { maxLength: 500 })}
                    </div>
                    {text('facts_period', t('report_form.description.facts_period'), { help: t('report_form.description.facts_period_help'), maxLength: 500 })}
                    {['is_recurring', 'is_ongoing', 'immediate_danger'].map((name) => (
                        <YesNo
                            key={name}
                            name={name}
                            label={t(`report_form.description.${name}`)}
                            value={data[name]}
                            onChange={(value) => setData(name, value)}
                            allowUnknown={name !== 'immediate_danger'}
                            error={errors[name]}
                        />
                    ))}
                    <EmergencyContacts />
                </>
            );

        case 'personnes':
            return (
                <>
                    {longText('people_involved', t('report_form.people.people_involved'), { help: t('report_form.people.people_involved_help'), maxLength: 5000 })}
                    {longText('witnesses', t('report_form.people.witnesses'), { help: t('report_form.people.witnesses_help'), rows: 3, maxLength: 5000 })}
                </>
            );

        case 'pieces-jointes':
            return (
                <>
                    <Alert icon={Paperclip} title={t('report_form.attachments.soon_title')}>
                        {t('report_form.attachments.soon_help')}
                    </Alert>
                    <div>
                        <h2 className="font-bold">{t('report_form.attachments.metadata_title')}</h2>
                        <p className="mt-1 text-muted">{t('report_form.attachments.metadata_help')}</p>
                    </div>
                </>
            );

        case 'consentement':
            return (
                <>
                    <div className="space-y-3">
                        {[
                            ['processing', true],
                            ['contact', true],
                            ['identity_disclosure', isConfidential],
                        ]
                            .filter(([, shown]) => shown)
                            .map(([purpose]) => (
                                <Checkbox
                                    key={purpose}
                                    name={`consent_${purpose}`}
                                    checked={data[`consent_${purpose}`]}
                                    onChange={(event) => setData(`consent_${purpose}`, event.target.checked)}
                                    label={
                                        <>
                                            {t(`reporting.consents.${purpose}.label`)}
                                            {purpose === 'contact' && <span className="ml-1.5 font-normal text-muted">{optional}</span>}
                                        </>
                                    }
                                    description={t(`reporting.consents.${purpose}.description`)}
                                    error={errors[`consent_${purpose}`]}
                                />
                            ))}
                    </div>
                    <p className="text-[15px] text-muted">{t('report_form.consent.before_submit')}</p>
                </>
            );

        default:
            return null;
    }
}

/* Oui / Non / Je ne sais pas, en boutons radio de même taille */
function YesNo({ name, label, value, onChange, allowUnknown, error }) {
    const t = useTranslator();
    const options = [
        { key: 'yes', value: true, label: t('report_form.yes') },
        { key: 'no', value: false, label: t('report_form.no') },
        ...(allowUnknown ? [{ key: 'unknown', value: null, label: t('report_form.unknown') }] : []),
    ];

    return (
        <fieldset>
            <legend className="mb-3 font-semibold">
                {label}
                <span className="ml-1.5 font-normal text-muted">{t('report_form.optional')}</span>
            </legend>
            <div className="flex flex-wrap gap-2">
                {options.map((option) => {
                    const checked = value === option.value;

                    return (
                        <label
                            key={option.key}
                            className={cx(
                                'inline-flex min-h-12 cursor-pointer items-center rounded-full border-2 px-5 font-semibold',
                                checked ? 'border-brand bg-brand-soft text-brand' : 'border-line text-ink hover:border-line-strong',
                                'has-[:focus-visible]:outline has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-[var(--focus)]',
                            )}
                        >
                            <input type="radio" name={name} checked={checked} onChange={() => onChange(option.value)} className="sr-only" />
                            {option.label}
                        </label>
                    );
                })}
            </div>
            {error && (
                <div className="mt-2">
                    <FieldError>{error}</FieldError>
                </div>
            )}
        </fieldset>
    );
}

Step.layout = (page) => <PublicLayout>{page}</PublicLayout>;
