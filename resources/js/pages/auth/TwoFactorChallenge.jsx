import { useForm } from '@inertiajs/react';
import { PageTitle } from '../../components/samm';
import { Button, Field, Input } from '../../components/ui';
import AuthLayout from '../../layouts/AuthLayout';
import { useTranslator } from '../../lib/i18n';

export default function TwoFactorChallenge({ action }) {
    const t = useTranslator();
    const form = useForm({ code: '', recovery_code: '' });

    return (
        <>
            <PageTitle area="manage" title={t('manage.two_factor.title')} />
            <h1 className="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{t('manage.two_factor.title')}</h1>
            <p className="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">{t('manage.two_factor.lead')}</p>

            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(action);
                }}
                noValidate
                className="mt-8 space-y-6"
            >
                <Field label={t('manage.two_factor.code')} error={form.errors.code}>
                    {(field) => (
                        <Input
                            {...field}
                            name="code"
                            inputMode="numeric"
                            pattern="[0-9]*"
                            autoComplete="one-time-code"
                            autoFocus
                            value={form.data.code}
                            onChange={(event) => form.setData('code', event.target.value)}
                            className="font-mono text-xl tracking-[0.3em]"
                        />
                    )}
                </Field>

                <details open={Boolean(form.errors.recovery_code) || undefined} className="rounded-lg bg-gray-50 p-4 ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/10">
                    <summary className="min-h-6 cursor-pointer text-sm font-medium text-gray-900 dark:text-white">{t('manage.two_factor.use_recovery')}</summary>
                    <div className="mt-4">
                        <Field label={t('manage.two_factor.recovery_code')} error={form.errors.recovery_code}>
                            {(field) => (
                                <Input {...field} name="recovery_code" autoComplete="off" value={form.data.recovery_code} onChange={(event) => form.setData('recovery_code', event.target.value)} className="font-mono" />
                            )}
                        </Field>
                    </div>
                </details>

                <Button type="submit" size="lg" className="w-full" disabled={form.processing}>
                    {t('manage.two_factor.submit')}
                </Button>
            </form>
        </>
    );
}

TwoFactorChallenge.layout = (page) => <AuthLayout>{page}</AuthLayout>;
