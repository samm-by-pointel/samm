import { useForm } from '@inertiajs/react';
import { PageTitle } from '../../components/samm';
import { Button, Field, Input } from '../../components/ui';
import AuthLayout from '../../layouts/AuthLayout';
import { useTranslator } from '../../lib/i18n';

export default function ResetPassword({ action, token, email }) {
    const t = useTranslator();
    const form = useForm({ token, email, password: '', password_confirmation: '' });

    return (
        <>
            <PageTitle area="manage" title={t('manage.auth.reset.title')} />
            <h1 className="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{t('manage.auth.reset.title')}</h1>
            <p className="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">{t('manage.auth.reset.rules')}</p>

            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(action, { onFinish: () => form.reset('password', 'password_confirmation') });
                }}
                noValidate
                className="mt-8 space-y-6"
            >
                <Field label={t('manage.auth.fields.email')} error={form.errors.email}>
                    {(field) => <Input {...field} type="email" name="email" autoComplete="username" value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} />}
                </Field>
                <Field label={t('manage.auth.fields.new_password')} error={form.errors.password}>
                    {(field) => <Input {...field} type="password" name="password" autoComplete="new-password" value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} />}
                </Field>
                <Field label={t('manage.auth.fields.password_confirmation')}>
                    {(field) => (
                        <Input {...field} type="password" name="password_confirmation" autoComplete="new-password" value={form.data.password_confirmation} onChange={(event) => form.setData('password_confirmation', event.target.value)} />
                    )}
                </Field>
                <Button type="submit" size="lg" className="w-full" disabled={form.processing}>
                    {t('manage.auth.reset.submit')}
                </Button>
            </form>
        </>
    );
}

ResetPassword.layout = (page) => <AuthLayout>{page}</AuthLayout>;
