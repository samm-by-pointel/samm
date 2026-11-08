<?php

namespace App\Http\Middleware;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Organizations\Contracts\OrganizationResolver;
use App\Modules\Organizations\Models\EmergencyContact;
use App\Modules\Organizations\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;

/**
 * Données communes à toutes les pages React : langue, textes, organisation,
 * messages flash et droits de navigation. Rien de ce qui est partagé ici ne
 * touche au contenu d'un signalement.
 */
class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /**
     * Groupes de traduction envoyés à toutes les pages ; l'espace de gestion
     * reçoit en plus les siens.
     */
    private const PUBLIC_GROUPS = ['public', 'report_form', 'reporting', 'tracking'];

    private const MANAGE_GROUPS = ['manage', 'audit'];

    /**
     * L'historique du navigateur garde les données de chaque page : en HTTPS,
     * Inertia le chiffre (l'API Web Crypto n'existe pas hors contexte sécurisé).
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->secure()) {
            Inertia::encryptHistory();
        }

        return parent::handle($request, $next);
    }

    public function share(Request $request): array
    {
        $organization = app(OrganizationResolver::class)->current();
        $user = $request->user();
        $isManageArea = $this->isManageArea($request);

        return [
            ...parent::share($request),
            'locale' => app()->getLocale(),
            'locales' => collect(config('samm.locales'))
                ->map(fn (string $name, string $code): array => [
                    'code' => $code,
                    'name' => $name,
                    'flag' => config('samm.locale_flags.'.$code),
                ])
                ->values(),
            'translations' => fn (): array => $this->translations($isManageArea),
            'site' => fn (): array => $this->site($organization),
            'emergencyContacts' => fn (): array => $isManageArea ? [] : EmergencyContact::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn (EmergencyContact $contact): array => [
                    'id' => $contact->uuid,
                    'label' => $contact->label(),
                    'number' => $contact->phone_number,
                    'dialable' => $contact->dialable(),
                ])
                ->all(),
            'status' => fn (): ?string => $request->session()->get('status'),
            'routes' => fn (): array => [
                'home' => route('public.home'),
                'locale' => route('locale.update'),
                'reportStart' => route('report.start'),
                'trackingLogin' => route('tracking.login'),
                'login' => route('login'),
                'logout' => route('logout'),
                'dashboard' => route('manage.dashboard'),
                'cases' => route('manage.cases.index'),
                'organization' => route('manage.organization.edit'),
                'audit' => route('manage.audit.index'),
            ],
            'auth' => fn (): ?array => $user ? [
                'name' => $user->name,
                'can' => [
                    'viewCases' => $user->can('viewAny', CaseFile::class),
                    'manageSettings' => $user->can('settings.manage'),
                    'viewAudit' => $user->can('audit.view'),
                ],
            ] : null,
        ];
    }

    private function isManageArea(Request $request): bool
    {
        return $request->routeIs('manage.*', 'login', 'password.*', 'two-factor.*', 'logout');
    }

    /**
     * @return array<string, mixed>
     */
    private function translations(bool $isManageArea): array
    {
        $groups = $isManageArea ? [...self::PUBLIC_GROUPS, ...self::MANAGE_GROUPS] : self::PUBLIC_GROUPS;

        return collect($groups)->mapWithKeys(fn (string $group): array => [$group => trans($group)])->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function site(?Organization $organization): array
    {
        return [
            'name' => $organization?->serviceName() ?: config('app.name'),
            'appName' => config('app.name'),
            'neutralTitle' => $organization?->effectiveNeutralTitle() ?? config('samm.neutral_title'),
            'logoUrl' => $organization?->hasLogo() ? $organization->logoUrl() : null,
            'quickExitUrl' => $organization?->effectiveQuickExitUrl() ?? config('samm.quick_exit_url'),
            'trackingSessionMinutes' => (int) config('samm.tracking.session_minutes'),
            'publisherUrl' => config('samm.publisher_url'),
        ];
    }
}
