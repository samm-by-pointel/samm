import { useForm, usePage } from '@inertiajs/react';
import { ArrowRight } from '../../components/icons';
import { IllustrationCode } from '../../components/illustrations';
import { PageTitle, PublicHero } from '../../components/samm';
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
        <>
            <PageTitle title={t('tracking.login.title')} />
            <PublicHero title={t('tracking.login.title')} lead={t('tracking.login.lead')} aside={<IllustrationCode className="w-60" />} />

            <Narrow className="-mt-8 pt-0 sm:pt-0">
                {status && <Alert className="mb-6">{status}</Alert>}

                <form onSubmit={submit} noValidate className="overflow-hidden rounded-[28px] border border-line bg-surface">
                    <div className="space-y-6 p-6 sm:p-8">
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
                                    className="font-mono text-[20px] tracking-[0.1em]"
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
                                    className="font-mono text-[20px] tracking-[0.1em]"
                                />
                            )}
                        </Field>
                    </div>

                    <div className="flex flex-wrap items-center gap-3 border-t border-line bg-surface-alt px-6 py-5 sm:px-8">
                        <Button type="submit" size="lg" disabled={form.processing}>
                            {t('tracking.login.submit')}
                            <ArrowRight size={18} weight="bold" aria-hidden="true" />
                        </Button>
                        <Button href={routes.home} variant="quiet">
                            {t('report_form.back_home')}
                        </Button>
                    </div>
                </form>
            </Narrow>
        </>
    );
}

Login.layout = (page) => <PublicLayout>{page}</PublicLayout>;
