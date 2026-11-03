import { router, useForm, usePage } from '@inertiajs/react';
import { Plus, Trash } from '../../../components/icons';
import { PageTitle } from '../../../components/samm';
import { Alert, Button, Field, Input, Select } from '../../../components/ui';
import ManageLayout, { PageHeader } from '../../../layouts/ManageLayout';
import { useTranslator } from '../../../lib/i18n';

function Section({ id, title, help, children }) {
    return (
        <section aria-labelledby={id} className="overflow-hidden rounded-[28px] border border-line bg-surface">
            <div className="border-b border-line bg-surface-alt px-6 py-4 sm:px-8">
                <h2 id={id} className="text-[20px] font-bold">
                    {title}
                </h2>
                {help && <p className="mt-1 text-[15px] text-muted">{help}</p>}
            </div>
            <div className="space-y-6 p-6 sm:p-8">{children}</div>
        </section>
    );
}

function CheckboxInline({ label, checked, onChange }) {
    return (
        <label className="inline-flex min-h-11 cursor-pointer items-center gap-2.5">
            <input type="checkbox" checked={checked} onChange={(event) => onChange(event.target.checked)} className="size-5 accent-[var(--samm)]" />
            {label}
        </label>
    );
}

export default function Edit({ organization, contacts, categories, locales, defaults, urls }) {
    const { status, errors } = usePage().props;
    const t = useTranslator();
    const optional = t('report_form.optional');

    const form = useForm({
        _method: 'put',
        name: organization.name,
        service_name: Object.fromEntries(locales.map(({ code }) => [code, organization.serviceName[code] ?? ''])),
        primary_color: organization.primaryColor,
        secondary_color: organization.secondaryColor,
        neutral_title: organization.neutralTitle,
        quick_exit_url: organization.quickExitUrl,
        default_locale: organization.defaultLocale,
        logo: null,
        remove_logo: false,
    });

    const submit = (event) => {
        event.preventDefault();
        form.post(urls.update, { forceFormData: true, preserveScroll: true, onSuccess: () => form.setData('logo', null) });
    };

    const input = (name, label, options = {}) => (
        <Field label={label} optional={options.optional ? optional : undefined} help={options.help} error={form.errors[name]}>
            {(field) => <Input {...field} name={name} value={form.data[name]} onChange={(event) => form.setData(name, event.target.value)} {...options.input} />}
        </Field>
    );

    return (
        <>
            <PageTitle area="manage" title={t('manage.organization.title')} />
            <PageHeader title={t('manage.organization.title')} lead={t('manage.organization.lead')} />

            {status && (
                <Alert tone="succes" className="mb-6">
                    {status}
                </Alert>
            )}

            <form onSubmit={submit} noValidate className="space-y-6">
                <Section id="identity-section" title={t('manage.organization.sections.identity')}>
                    {input('name', t('manage.organization.fields.name'), { input: { maxLength: 120, required: true } })}
                    <fieldset>
                        <legend className="font-semibold">
                            {t('manage.organization.fields.service_name')}
                            <span className="ml-1.5 font-normal text-muted">{optional}</span>
                        </legend>
                        <p className="mt-1 text-[15px] text-muted">{t('manage.organization.fields.service_name_help')}</p>
                        <div className="mt-3 grid gap-4 sm:grid-cols-2">
                            {locales.map(({ code, name }) => (
                                <Field key={code} label={name} error={form.errors[`service_name.${code}`]}>
                                    {(field) => (
                                        <Input
                                            {...field}
                                            lang={code}
                                            maxLength={120}
                                            value={form.data.service_name[code]}
                                            onChange={(event) => form.setData('service_name', { ...form.data.service_name, [code]: event.target.value })}
                                        />
                                    )}
                                </Field>
                            ))}
                        </div>
                    </fieldset>
                </Section>

                <Section id="appearance-section" title={t('manage.organization.sections.appearance')} help={t('manage.organization.fields.colors_help')}>
                    <div className="grid gap-6 sm:grid-cols-2">
                        {[
                            ['primary_color', defaults.primaryColor, 'var(--samm)'],
                            ['secondary_color', defaults.secondaryColor, 'var(--terre)'],
                        ].map(([name, placeholder, fallback]) => (
                            <Field key={name} label={t(`manage.organization.fields.${name}`)} error={form.errors[name]}>
                                {(field) => (
                                    <div className="flex gap-3">
                                        <Input
                                            {...field}
                                            name={name}
                                            value={form.data[name]}
                                            onChange={(event) => form.setData(name, event.target.value)}
                                            placeholder={placeholder}
                                            maxLength={7}
                                            pattern="#[0-9A-Fa-f]{6}"
                                            autoComplete="off"
                                            className="font-mono"
                                        />
                                        <span aria-hidden="true" className="size-12 shrink-0 rounded-xl border border-line-strong" style={{ background: /^#[0-9a-f]{6}$/i.test(form.data[name]) ? form.data[name] : fallback }} />
                                    </div>
                                )}
                            </Field>
                        ))}
                    </div>

                    <Field label={t('manage.organization.fields.logo')} help={t('manage.organization.fields.logo_help')} error={form.errors.logo}>
                        {(field) => (
                            <div className="space-y-3">
                                {organization.logoUrl && (
                                    <div className="flex flex-wrap items-center gap-4">
                                        <img src={organization.logoUrl} alt={t('manage.organization.fields.logo_current')} className="h-14 w-auto max-w-[200px] rounded-xl border border-line bg-surface object-contain p-1.5" />
                                        <CheckboxInline label={t('manage.organization.fields.remove_logo')} checked={form.data.remove_logo} onChange={(value) => form.setData('remove_logo', value)} />
                                    </div>
                                )}
                                <input
                                    {...field}
                                    type="file"
                                    name="logo"
                                    accept="image/png,image/jpeg,image/webp"
                                    onChange={(event) => form.setData('logo', event.target.files[0] ?? null)}
                                    className="block w-full text-[15px] file:mr-4 file:min-h-11 file:cursor-pointer file:rounded-xl file:border-0 file:bg-brand-soft file:px-4 file:font-semibold file:text-brand"
                                />
                            </div>
                        )}
                    </Field>
                </Section>

                <Section id="public-section" title={t('manage.organization.sections.public')}>
                    {input('neutral_title', t('manage.organization.fields.neutral_title'), {
                        optional: true,
                        help: t('manage.organization.fields.neutral_title_help'),
                        input: { maxLength: 80 },
                    })}
                    {input('quick_exit_url', t('manage.organization.fields.quick_exit_url'), {
                        optional: true,
                        help: t('manage.organization.fields.quick_exit_url_help', { default: defaults.quickExitUrl }),
                        input: { type: 'url', placeholder: 'https://' },
                    })}
                    <Field label={t('manage.organization.fields.default_locale')} error={form.errors.default_locale}>
                        {(field) => (
                            <Select {...field} name="default_locale" value={form.data.default_locale} onChange={(event) => form.setData('default_locale', event.target.value)} className="sm:max-w-xs">
                                {locales.map(({ code, name }) => (
                                    <option key={code} value={code}>
                                        {name}
                                    </option>
                                ))}
                            </Select>
                        )}
                    </Field>
                </Section>

                <div className="sticky bottom-4 z-10 flex justify-end">
                    <Button type="submit" size="lg" disabled={form.processing} className="shadow-[0_10px_30px_-10px_rgb(28_36_34/0.5)]">
                        {t('manage.organization.save')}
                    </Button>
                </div>
            </form>

            <div className="mt-10 space-y-6">
                <Section id="emergency-section" title={t('manage.organization.emergency.title')} help={t('manage.organization.emergency.help')}>
                    {errors?.emergency && (
                        <Alert tone="urgent" title={t('report_form.errors.title')}>
                            {Object.values(errors.emergency)[0]}
                        </Alert>
                    )}
                    {contacts.map((contact) => (
                        <ContactRow key={contact.uuid} contact={contact} locales={locales} />
                    ))}
                    <ContactRow locales={locales} storeUrl={urls.contactStore} nextPosition={contacts.length + 1} />
                </Section>

                <Section id="categories-section" title={t('manage.organization.categories.title')} help={t('manage.organization.categories.help')}>
                    {errors?.categories && (
                        <Alert tone="urgent" title={t('report_form.errors.title')}>
                            {Object.values(errors.categories)[0]}
                        </Alert>
                    )}
                    {categories.map((category) => (
                        <CategoryRow key={category.uuid} category={category} locales={locales} />
                    ))}
                    <CategoryRow locales={locales} storeUrl={urls.categoryStore} nextPosition={categories.length + 1} />
                </Section>
            </div>
        </>
    );
}

/* Un numéro d'urgence : modification sur place, ou ajout pour la dernière ligne */
function ContactRow({ contact, locales, storeUrl, nextPosition }) {
    const t = useTranslator();
    const isNew = !contact;
    const form = useForm({
        ...(isNew ? {} : { _method: 'put' }),
        label: Object.fromEntries(locales.map(({ code }) => [code, contact?.label[code] ?? ''])),
        phone_number: contact?.phoneNumber ?? '',
        position: contact?.position ?? nextPosition,
        is_active: contact?.isActive ?? true,
    });

    const save = (event) => {
        event.preventDefault();
        form.post(isNew ? storeUrl : contact.updateUrl, {
            errorBag: 'emergency',
            preserveScroll: true,
            onSuccess: () => isNew && form.reset(),
        });
    };

    return (
        <form onSubmit={save} noValidate className={isNew ? 'rounded-2xl border-2 border-dashed border-line p-5' : 'rounded-2xl bg-surface-alt p-5'}>
            {isNew && <h3 className="mb-4 font-bold">{t('manage.organization.emergency.add')}</h3>}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-[repeat(2,minmax(0,1fr))_minmax(0,1fr)_110px]">
                {locales.map(({ code, name }) => (
                    <Field key={code} label={`${t('manage.organization.emergency.label')} (${name})`}>
                        {(field) => <Input {...field} lang={code} maxLength={80} value={form.data.label[code]} onChange={(event) => form.setData('label', { ...form.data.label, [code]: event.target.value })} />}
                    </Field>
                ))}
                <Field label={t('manage.organization.emergency.phone_number')}>
                    {(field) => <Input {...field} maxLength={32} required className="font-mono" value={form.data.phone_number} onChange={(event) => form.setData('phone_number', event.target.value)} />}
                </Field>
                <Field label={t('manage.organization.emergency.position')}>
                    {(field) => <Input {...field} type="number" min={0} max={999} value={form.data.position} onChange={(event) => form.setData('position', event.target.value)} />}
                </Field>
            </div>
            <div className="mt-4 flex flex-wrap items-center gap-3">
                <CheckboxInline label={t('manage.organization.emergency.active')} checked={form.data.is_active} onChange={(value) => form.setData('is_active', value)} />
                <div className="ml-auto flex gap-2">
                    {!isNew && (
                        <Button variant="quiet" size="sm" className="text-urgent hover:bg-urgent-soft" onClick={() => router.delete(contact.deleteUrl, { preserveScroll: true })}>
                            <Trash size={18} aria-hidden="true" />
                            {t('manage.organization.emergency.delete')}
                        </Button>
                    )}
                    <Button type="submit" variant="secondary" size="sm" disabled={form.processing}>
                        {isNew && <Plus size={18} aria-hidden="true" />}
                        {isNew ? t('manage.organization.emergency.submit') : t('manage.organization.emergency.update')}
                    </Button>
                </div>
            </div>
        </form>
    );
}

/* Une catégorie de signalement : nom et description dans chaque langue */
function CategoryRow({ category, locales, storeUrl, nextPosition }) {
    const t = useTranslator();
    const isNew = !category;
    const form = useForm({
        ...(isNew ? {} : { _method: 'put' }),
        name: Object.fromEntries(locales.map(({ code }) => [code, category?.name[code] ?? ''])),
        ...(isNew ? {} : { description: Object.fromEntries(locales.map(({ code }) => [code, category.description[code] ?? ''])) }),
        position: category?.position ?? nextPosition,
        is_active: category?.isActive ?? true,
    });

    const save = (event) => {
        event.preventDefault();
        form.post(isNew ? storeUrl : category.updateUrl, {
            errorBag: 'categories',
            preserveScroll: true,
            onSuccess: () => isNew && form.reset(),
        });
    };

    return (
        <form onSubmit={save} noValidate className={isNew ? 'rounded-2xl border-2 border-dashed border-line p-5' : 'rounded-2xl bg-surface-alt p-5'}>
            {isNew && <h3 className="mb-4 font-bold">{t('manage.organization.categories.add')}</h3>}
            <div className="grid gap-4 sm:grid-cols-2">
                {locales.map(({ code, name }) => (
                    <Field key={code} label={`${t('manage.organization.categories.name')} (${name})`}>
                        {(field) => <Input {...field} lang={code} maxLength={120} value={form.data.name[code]} onChange={(event) => form.setData('name', { ...form.data.name, [code]: event.target.value })} />}
                    </Field>
                ))}
                {!isNew &&
                    locales.map(({ code, name }) => (
                        <Field key={`d-${code}`} label={`${t('manage.organization.categories.description')} (${name})`}>
                            {(field) => (
                                <Input {...field} lang={code} maxLength={500} value={form.data.description[code]} onChange={(event) => form.setData('description', { ...form.data.description, [code]: event.target.value })} />
                            )}
                        </Field>
                    ))}
            </div>
            <div className="mt-4 flex flex-wrap items-end gap-4">
                <Field label={t('manage.organization.categories.position')} className="w-28">
                    {(field) => <Input {...field} type="number" min={0} max={999} value={form.data.position} onChange={(event) => form.setData('position', event.target.value)} />}
                </Field>
                <CheckboxInline label={t('manage.organization.categories.active')} checked={form.data.is_active} onChange={(value) => form.setData('is_active', value)} />
                <Button type="submit" variant="secondary" size="sm" className="ml-auto" disabled={form.processing}>
                    {isNew && <Plus size={18} aria-hidden="true" />}
                    {isNew ? t('manage.organization.categories.submit') : t('manage.organization.categories.update')}
                </Button>
            </div>
        </form>
    );
}

Edit.layout = (page) => <ManageLayout>{page}</ManageLayout>;
