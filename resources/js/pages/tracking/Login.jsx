import { useForm, usePage } from '@inertiajs/react';
import { Key } from '@phosphor-icons/react';
import { PageTitle } from '../../components/samm';
import { Alert, Button, Field, Input } from '../../components/ui';
import PublicLayout, { Narrow } from '../../layouts/PublicLayout';
import { useTranslator } from '../../lib/i18n';

export default function Login({ action }) {
    const { routes, status } = usePage().props;
    const t = useTranslator();
    const form = useForm({ reference: '', secret: '' });

    const submit = (event) => {
        event.preventDefault();
        form.post(action, { onFinish: () => form.reset('secret') });
    };

    return (
        <Narrow>
            <PageTitle title={t('tracking.login.title')} />

            <span aria-hidden="true" className="grid size-14 place-items-center rounded-2xl bg-accent-soft text-accent">
                <Key size={30} weight="duotone" />
            </span>
            <h1 className="mt-6 text-[clamp(32px,5vw,44px)] leading-tight font-bold">{t('tracking.login.title')}</h1>
            <p className="mt-3 text-[19px] text-muted">{t('tracking.login.lead')}</p>

            {status && <Alert className="mt-6">{status}</Alert>}

            <form onSubmit={submit} noValidate className="mt-8 space-y-6 rounded-3xl border border-line p-6 sm:p-8">
                {form.errors.reference && (
                    <Alert tone="urgent" title={t('tracking.login.failed_title')}>
                        {form.errors.reference}
                    </Alert>
                )}

                <Field label={t('tracking.login.reference')}>
                    {(field) => (
                        <Input
                            {...field}
                            name="reference"
                            value={form.data.reference}
                            onChange={(event) => form.setData('reference', event.target.value)}
                            placeholder="SMM-XXXX-XXXX"
                            autoComplete="off"
                            autoCapitalize="characters"
                            spellCheck={false}
                            className="font-mono text-[20px] tracking-[0.08em]"
                        />
                    )}
                </Field>

                <Field label={t('tracking.login.secret')} help={t('tracking.login.help')} error={form.errors.secret}>
                    {(field) => (
                        <Input
                            {...field}
                            type="password"
                            name="secret"
                            value={form.data.secret}
                            onChange={(event) => form.setData('secret', event.target.value)}
                            autoComplete="off"
                            className="font-mono text-[20px] tracking-[0.08em]"
                        />
                    )}
                </Field>

                <div className="flex flex-wrap items-center gap-3 pt-2">
                    <Button type="submit" size="lg" disabled={form.processing}>
                        {t('tracking.login.submit')}
                    </Button>
                    <Button href={routes.home} variant="quiet">
                        {t('report_form.back_home')}
                    </Button>
                </div>
            </form>
        </Narrow>
    );
}

Login.layout = (page) => <PublicLayout>{page}</PublicLayout>;
