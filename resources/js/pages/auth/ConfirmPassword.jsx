import { ArrowRight } from '../../components/icons';
import { useForm } from '@inertiajs/react';
import { PageTitle } from '../../components/samm';
import { Button, Field, Input } from '../../components/ui';
import AuthLayout from '../../layouts/AuthLayout';
import { useTranslator } from '../../lib/i18n';

export default function ConfirmPassword({ action }) {
    const t = useTranslator();
    const form = useForm({ password: '' });

    return (
        <>
            <PageTitle area="manage" title={t('manage.confirm_password.title')} />
            <h1 className="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{t('manage.confirm_password.title')}</h1>
            <p className="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">{t('manage.confirm_password.lead')}</p>

            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(action, { onFinish: () => form.reset('password') });
                }}
                noValidate
                className="mt-8 space-y-6"
            >
                <Field label={t('manage.auth.fields.password')} error={form.errors.password}>
                    {(field) => <Input {...field} type="password" name="password" autoComplete="current-password" autoFocus value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} />}
                </Field>
                <Button type="submit" size="lg" className="w-full" disabled={form.processing}>
                    {t('manage.confirm_password.submit')}
                    <ArrowRight size={18} weight="bold" aria-hidden="true" />
                </Button>
            </form>
        </>
    );
}

ConfirmPassword.layout = (page) => <AuthLayout>{page}</AuthLayout>;
