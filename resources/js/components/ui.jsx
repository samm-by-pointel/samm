import { Link } from '@inertiajs/react';
import { CheckCircle, Info, WarningCircle } from './icons';
import { useId } from 'react';

export function cx(...classes) {
    return classes.filter(Boolean).join(' ');
}

/* Boutons - charte §12.3 : hauteur ≥ 44px, libellé explicite */
const BUTTON_VARIANTS = {
    primary: 'bg-brand text-on-brand hover:brightness-110 active:brightness-95',
    secondary: 'border border-line-strong bg-surface text-ink hover:bg-surface-alt',
    quiet: 'text-brand hover:bg-brand-soft',
    danger: 'bg-urgent text-white hover:brightness-110',
    light: 'bg-white text-[#1C1A33] hover:bg-white/90',
};

const BUTTON_SIZES = {
    md: 'min-h-12 px-5 text-[16px]',
    lg: 'min-h-14 px-7 text-[17px]',
    sm: 'min-h-11 px-4 text-[15px]',
};

export function Button({ variant = 'primary', size = 'md', href, external = false, className, children, ...props }) {
    const classes = cx(
        'inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-[filter,background-color] disabled:cursor-not-allowed disabled:opacity-60',
        BUTTON_VARIANTS[variant],
        BUTTON_SIZES[size],
        className,
    );

    if (href && external) {
        return (
            <a href={href} className={classes} {...props}>
                {children}
            </a>
        );
    }

    if (href) {
        return (
            <Link href={href} className={classes} {...props}>
                {children}
            </Link>
        );
    }

    return (
        <button type="button" className={classes} {...props}>
            {children}
        </button>
    );
}

/* Champs : libellé, aide, erreur reliés au contrôle pour les lecteurs d'écran */
export function Field({ label, optional, help, error, children, className, as = 'div' }) {
    const id = useId();
    const helpId = help ? `${id}-help` : undefined;
    const errorId = error ? `${id}-error` : undefined;
    const Tag = as;
    const LabelTag = as === 'fieldset' ? 'legend' : 'label';

    return (
        <Tag className={cx('space-y-2', className)}>
            <LabelTag htmlFor={as === 'fieldset' ? undefined : id} className="block font-semibold text-ink">
                {label}
                {optional && <span className="ml-1.5 font-normal text-muted">{optional}</span>}
            </LabelTag>
            {help && (
                <p id={helpId} className="text-[15px] leading-snug text-muted">
                    {help}
                </p>
            )}
            {children({ id, 'aria-describedby': cx(helpId, errorId) || undefined, 'aria-invalid': error ? true : undefined })}
            {error && <FieldError id={errorId}>{error}</FieldError>}
        </Tag>
    );
}

export function FieldError({ id, children }) {
    return (
        <p id={id} className="flex items-start gap-1.5 text-[15px] font-semibold text-urgent">
            <WarningCircle size={18} weight="bold" className="mt-0.5 shrink-0" aria-hidden="true" />
            {children}
        </p>
    );
}

export function Input({ className, ...props }) {
    return <input className={cx('field-control', className)} {...props} />;
}

export function Textarea({ className, ...props }) {
    return <textarea className={cx('field-control', className)} {...props} />;
}

export function Select({ className, children, ...props }) {
    return (
        <select className={cx('field-control appearance-auto pr-3', className)} {...props}>
            {children}
        </select>
    );
}

export function Checkbox({ label, description, error, className, ...props }) {
    const id = useId();

    return (
        <div className={className}>
            <label
                htmlFor={id}
                className={cx(
                    'flex cursor-pointer items-start gap-3 rounded-2xl border bg-surface p-4',
                    error ? 'border-urgent' : 'border-line',
                )}
            >
                <input id={id} type="checkbox" className="mt-0.5 size-5 shrink-0 accent-[var(--samm)]" aria-invalid={error ? true : undefined} {...props} />
                <span>
                    <span className="block font-semibold">{label}</span>
                    {description && <span className="mt-1 block text-[15px] leading-snug text-muted">{description}</span>}
                </span>
            </label>
            {error && (
                <div className="mt-2">
                    <FieldError>{error}</FieldError>
                </div>
            )}
        </div>
    );
}

/**
 * Choix exclusif présenté en cartes (charte §12.4) : toutes de même poids,
 * aucune n'est mise en avant.
 */
export function RadioCards({ name, value, onChange, options, legend, error, columns = 1 }) {
    const id = useId();

    return (
        <fieldset aria-describedby={error ? `${id}-error` : undefined}>
            {legend && <legend className="mb-3 font-semibold">{legend}</legend>}
            <div className={cx('grid gap-3', columns === 2 && 'sm:grid-cols-2')}>
                {options.map((option) => {
                    const Icon = option.icon;
                    const checked = value === option.value;

                    return (
                        <label
                            key={option.value}
                            className={cx(
                                'flex cursor-pointer items-start gap-4 rounded-2xl border-2 p-4 transition-colors',
                                checked ? 'border-brand bg-brand-soft' : 'border-line bg-surface hover:border-line-strong',
                                'has-[:focus-visible]:outline has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-[var(--focus)]',
                            )}
                        >
                            <input
                                type="radio"
                                name={name}
                                value={option.value}
                                checked={checked}
                                onChange={() => onChange(option.value)}
                                className="sr-only"
                            />
                            {Icon && <Icon size={28} weight={checked ? 'fill' : 'regular'} className="mt-0.5 shrink-0 text-brand" aria-hidden="true" />}
                            <span className="min-w-0 flex-1">
                                <span className="block text-[17px] font-semibold">{option.label}</span>
                                {option.description && <span className="mt-1 block text-[15px] leading-snug text-muted">{option.description}</span>}
                            </span>
                            <span
                                aria-hidden="true"
                                className={cx(
                                    'mt-1 grid size-5 shrink-0 place-items-center rounded-full border-2',
                                    checked ? 'border-brand' : 'border-line-strong',
                                )}
                            >
                                {checked && <span className="size-2.5 rounded-full bg-brand" />}
                            </span>
                        </label>
                    );
                })}
            </div>
            {error && (
                <div className="mt-3">
                    <FieldError id={`${id}-error`}>{error}</FieldError>
                </div>
            )}
        </fieldset>
    );
}

/* Alertes - charte §12.11 : toujours icône + titre + phrase */
const ALERT_TONES = {
    info: { box: 'bg-info-soft', icon: 'text-info', Icon: Info },
    succes: { box: 'bg-succes-soft', icon: 'text-succes', Icon: CheckCircle },
    urgent: { box: 'bg-urgent-soft', icon: 'text-urgent', Icon: WarningCircle },
};

export function Alert({ tone = 'info', title, icon, children, className }) {
    const { box, icon: iconClass, Icon } = ALERT_TONES[tone];
    const Glyph = icon ?? Icon;

    return (
        <div role={tone === 'urgent' ? 'alert' : 'status'} className={cx('flex gap-3 rounded-2xl p-4 text-[16px] leading-snug', box, className)}>
            <Glyph size={24} weight="fill" className={cx('shrink-0', iconClass)} aria-hidden="true" />
            <div>
                {title && <p className={cx('font-semibold', iconClass)}>{title}</p>}
                {children && <div className={title ? 'mt-0.5' : undefined}>{children}</div>}
            </div>
        </div>
    );
}

export function Card({ as: Tag = 'section', className, children, ...props }) {
    return (
        <Tag className={cx('rounded-3xl border border-line bg-surface p-6 sm:p-8', className)} {...props}>
            {children}
        </Tag>
    );
}

/* Badges de statut - charte §12.7 : couleurs verrouillées, libellé toujours écrit */
const STATUS_TONES = {
    new: 'bg-sunken text-ink',
    to_qualify: 'bg-info-soft text-info',
    in_progress: 'bg-brand-soft text-brand',
    awaiting_information: 'bg-attente-soft text-attente',
    referred: 'bg-accent-soft text-accent',
    resolving: 'bg-brand-soft text-brand',
    closed: 'bg-sunken text-muted',
    archived: 'bg-sunken text-muted',
    urgent: 'bg-urgent-soft text-urgent',
};

export function Badge({ tone = 'new', children, className }) {
    return (
        <span className={cx('inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[14px] font-semibold leading-5', STATUS_TONES[tone], className)}>
            <span aria-hidden="true" className="size-2 rounded-full bg-current" />
            {children}
        </span>
    );
}

export function StatusBadge({ status }) {
    if (!status) {
        return null;
    }

    return <Badge tone={status.value}>{status.label}</Badge>;
}

export function DefinitionList({ items, className }) {
    return (
        <dl className={cx('grid gap-x-8 gap-y-4 sm:grid-cols-2', className)}>
            {items.map(({ term, detail, wide, mono }) => (
                <div key={term} className={wide ? 'sm:col-span-2' : undefined}>
                    <dt className="text-[14px] font-semibold text-muted">{term}</dt>
                    <dd className={cx('mt-0.5 whitespace-pre-line', mono && 'font-mono tracking-wide')}>{detail}</dd>
                </div>
            ))}
        </dl>
    );
}
