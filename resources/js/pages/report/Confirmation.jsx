import { usePage } from '@inertiajs/react';
import { CheckCircle, Copy, DownloadSimple } from '@phosphor-icons/react';
import { useState } from 'react';
import { CodeTicket, PageTitle } from '../../components/samm';
import { Button } from '../../components/ui';
import PublicLayout, { Narrow } from '../../layouts/PublicLayout';
import { useTranslator } from '../../lib/i18n';

/**
 * Écran de confirmation (charte §12.6) : les deux codes, affichés une seule fois,
 * à copier ou télécharger. Jamais envoyés par e-mail.
 */
export default function Confirmation({ reference, secret, downloadText }) {
    const { routes } = usePage().props;
    const t = useTranslator();
    const [copyStatus, setCopyStatus] = useState('');

    const copy = async () => {
        try {
            await navigator.clipboard.writeText(downloadText);
            setCopyStatus(t('report_form.confirmation.copied'));
        } catch {
            setCopyStatus(downloadText);
        }
    };

    return (
        <Narrow>
            <PageTitle title={t('report_form.confirmation.title')} />

            <div role="status" className="flex items-start gap-4">
                <CheckCircle size={44} weight="fill" className="shrink-0 text-succes" aria-hidden="true" />
                <div>
                    <h1 className="text-[clamp(28px,4.5vw,38px)] leading-tight font-bold">{t('report_form.confirmation.title')}</h1>
                    <p className="mt-3 text-[19px] leading-relaxed text-muted">{t('report_form.confirmation.lead')}</p>
                </div>
            </div>

            <section aria-labelledby="codes-title" className="mt-10">
                <h2 id="codes-title" className="text-[22px] font-bold">
                    {t('report_form.confirmation.codes_title')}
                </h2>
                <p className="mt-2 text-muted">{t('report_form.confirmation.codes_help')}</p>

                <CodeTicket
                    className="mt-6"
                    rows={[
                        { id: 'samm-reference', label: t('report_form.confirmation.reference'), value: reference },
                        { id: 'samm-secret', label: t('report_form.confirmation.secret'), value: secret },
                    ]}
                />

                <div className="mt-6 flex flex-wrap gap-3">
                    <Button onClick={copy}>
                        <Copy size={20} aria-hidden="true" />
                        {t('report_form.confirmation.copy')}
                    </Button>
                    <Button href={`data:text/plain;charset=utf-8,${encodeURIComponent(downloadText)}`} external download="samm-codes.txt" variant="secondary">
                        <DownloadSimple size={20} aria-hidden="true" />
                        {t('report_form.confirmation.download')}
                    </Button>
                </div>
                <p role="status" className="mt-3 text-[15px] whitespace-pre-line text-muted">
                    {copyStatus}
                </p>
            </section>

            <section className="mt-10 rounded-3xl bg-surface-alt p-6 sm:p-8">
                <h2 className="text-[20px] font-bold">{t('report_form.confirmation.next_title')}</h2>
                <p className="mt-2">{t('report_form.confirmation.next_help')}</p>
                <div className="mt-5 flex flex-wrap gap-3">
                    <Button href={routes.trackingLogin} variant="secondary">
                        {t('public.home.track_cta')}
                    </Button>
                    <Button href={routes.home} variant="quiet">
                        {t('report_form.back_home')}
                    </Button>
                </div>
            </section>
        </Narrow>
    );
}

Confirmation.layout = (page) => <PublicLayout>{page}</PublicLayout>;
