import { usePage } from '@inertiajs/react';
import { Hourglass } from '@phosphor-icons/react';
import { PageTitle } from '../components/samm';
import { Button } from '../components/ui';
import PublicLayout, { Narrow } from '../layouts/PublicLayout';
import { useTranslator } from '../lib/i18n';

export default function Error({ status }) {
    const { routes } = usePage().props;
    const t = useTranslator();

    return (
        <Narrow>
            <PageTitle title={t(`public.errors.${status}.title`)} />
            <Hourglass size={48} weight="duotone" className="text-brand" aria-hidden="true" />
            <h1 className="mt-6 text-[clamp(30px,4.5vw,40px)] leading-tight font-bold">{t(`public.errors.${status}.title`)}</h1>
            <p className="mt-3 text-[19px] text-muted">{t(`public.errors.${status}.lead`)}</p>
            <Button href={routes.home} variant="secondary" className="mt-8">
                {t('public.errors.back_home')}
            </Button>
        </Narrow>
    );
}

Error.layout = (page) => <PublicLayout>{page}</PublicLayout>;
