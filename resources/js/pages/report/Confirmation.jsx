import { usePage } from '@inertiajs/react';
import { CheckCircle, Copy, DownloadSimple } from '../../components/icons';
import { useState } from 'react';
import { CodeTicket, PageTitle, PublicHero } from '../../components/samm';
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
        <>
            <PageTitle title={t('report_form.confirmation.title')} />
            <PublicHero
                title={t('report_form.confirmation.title')}
                lead={t('report_form.confirmation.lead')}
                eyebrow={
                    <span aria-hidden="true" className="mb-5 grid size-16 place-items-center rounded-full bg-succes-soft">
                        <CheckCircle size={40} weight="fill" className="text-succes" />
                    </span>
                }
            />

            <Narrow className="-mt-8 pt-0 sm:pt-0">
                <section aria-labelledby="codes-title" role="status">
                    <h2 id="codes-title" className="sr-only">
                        {t('report_form.confirmation.codes_title')}
                    </h2>
                    <CodeTicket
                        title={t('report_form.confirmation.codes_title')}
                        rows={[
                            { id: 'samm-reference', label: t('report_form.confirmation.reference'), value: reference },
                            { id: 'samm-secret', label: t('report_form.confirmation.secret'), value: secret },
                        ]}
                        caption={t('report_form.confirmation.codes_help')}
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
                    <p aria-live="polite" className="mt-3 text-[15px] whitespace-pre-line text-muted">
                        {copyStatus}
                    </p>
                </section>

                <section className="mt-10 rounded-[28px] border border-line bg-surface-alt p-6 sm:p-8">
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
        </>
    );
}

Confirmation.layout = (page) => <PublicLayout>{page}</PublicLayout>;
