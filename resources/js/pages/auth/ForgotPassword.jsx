import { useForm, usePage } from '@inertiajs/react';
import { PageTitle } from '../../components/samm';
import { Alert, Button, Field, Input } from '../../components/ui';
import AuthLayout from '../../layouts/AuthLayout';
import { useTranslator } from '../../lib/i18n';

export default function ForgotPassword({ action, loginUrl }) {
    const { status } = usePage().props;
    const t = useTranslator();
    const form = useForm({ email: '' });

    return (
        <>
            <PageTitle area="manage" title={t('manage.auth.forgot.title')} />
            <h1 className="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{t('manage.auth.forgot.title')}</h1>
            <p className="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">{t('manage.auth.forgot.lead')}</p>

            {status && (
                <Alert tone="success" className="mt-5">
                    {status}
                </Alert>
            )}

            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(action);
                }}
                noValidate
                className="mt-8 space-y-6"
            >
                <Field label={t('manage.auth.fields.email')} error={form.errors.email}>
                    {(field) => <Input {...field} type="email" name="email" autoComplete="username" autoFocus value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} />}
                </Field>
                <div className="flex flex-wrap items-center gap-3">
                    <Button type="submit" disabled={form.processing}>
                        {t('manage.auth.forgot.submit')}
                    </Button>
                    <Button href={loginUrl} variant="quiet">
                        {t('manage.auth.forgot.back')}
                    </Button>
                </div>
            </form>
        </>
    );
}

ForgotPassword.layout = (page) => <AuthLayout>{page}</AuthLayout>;
