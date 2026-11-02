import { usePage } from '@inertiajs/react';
import { Hourglass } from '../components/icons';
import { PageTitle, PublicHero } from '../components/samm';
import { Button } from '../components/ui';
import PublicLayout, { Narrow } from '../layouts/PublicLayout';
import { useTranslator } from '../lib/i18n';

export default function Error({ status }) {
    const { routes } = usePage().props;
    const t = useTranslator();

    return (
        <>
            <PageTitle title={t(`public.errors.${status}.title`)} />
            <PublicHero
                title={t(`public.errors.${status}.title`)}
                lead={t(`public.errors.${status}.lead`)}
                eyebrow={
                    <span aria-hidden="true" className="mb-5 grid size-16 place-items-center rounded-full bg-surface">
                        <Hourglass size={36} className="text-brand" />
                    </span>
                }
            />
            <Narrow className="-mt-8 pt-0 sm:pt-0">
                <Button href={routes.home} variant="secondary">
                    {t('public.errors.back_home')}
                </Button>
            </Narrow>
        </>
    );
}

Error.layout = (page) => <PublicLayout>{page}</PublicLayout>;
