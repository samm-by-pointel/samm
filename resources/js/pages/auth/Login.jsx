import { Link, useForm, usePage } from '@inertiajs/react';
import { ArrowRight } from '../../components/icons';
import { PageTitle } from '../../components/samm';
import { Alert, Button, Field, Input } from '../../components/ui';
import AuthLayout from '../../layouts/AuthLayout';
import { useTranslator } from '../../lib/i18n';

export default function Login({ action, forgotUrl }) {
    const { status } = usePage().props;
    const t = useTranslator();
    const form = useForm({ email: '', password: '', remember: false });

    const submit = (event) => {
        event.preventDefault();
        form.post(action, { onFinish: () => form.reset('password') });
    };

    return (
        <>
            <PageTitle area="manage" title={t('manage.auth.login.title')} />
            <h1 className="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{t('manage.auth.login.title')}</h1>

            {status && (
                <Alert tone="success" className="mt-5">
                    {status}
                </Alert>
            )}

            <form onSubmit={submit} noValidate className="mt-8 space-y-6">
                <Field label={t('manage.auth.fields.email')} error={form.errors.email}>
                    {(field) => <Input {...field} type="email" name="email" autoComplete="username" autoFocus value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} />}
                </Field>
                <Field label={t('manage.auth.fields.password')} error={form.errors.password}>
                    {(field) => <Input {...field} type="password" name="password" autoComplete="current-password" value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} />}
                </Field>
                <label className="flex min-h-11 cursor-pointer items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="remember" checked={form.data.remember} onChange={(event) => form.setData('remember', event.target.checked)} className="size-4.5 accent-[var(--samm)]" />
                    {t('manage.auth.login.remember')}
                </label>
                <Button type="submit" size="lg" className="w-full" disabled={form.processing}>
                    {t('manage.auth.login.submit')}
                    <ArrowRight size={18} weight="bold" aria-hidden="true" />
                </Button>
                {forgotUrl && (
                    <p className="text-center">
                        <Link href={forgotUrl} className="text-sm font-semibold text-brand-text hover:underline">
                            {t('manage.auth.login.forgot')}
                        </Link>
                    </p>
                )}
            </form>
        </>
    );
}

Login.layout = (page) => <AuthLayout>{page}</AuthLayout>;
