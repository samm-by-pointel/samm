<?php

namespace Database\Seeders;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * The six system roles of the specification (§9) with their default
     * permissions. Existing roles are left untouched so that an
     * administrator's changes survive a reseed.
     *
     * @return array<string, array{name: array{fr: string, en: string}, description: array{fr: string, en: string}, permissions: array<int, Permission>}>
     */
    public static function matrix(): array
    {
        $caseManager = [
            Permission::ViewCaseList,
            Permission::ViewAssignedCases,
            Permission::ViewIdentities,
            Permission::ViewEvidence,
            Permission::DownloadEvidence,
            Permission::ReplyToMessages,
            Permission::CreateInternalNotes,
            Permission::UpdateCaseStatus,
            Permission::CloseCases,
        ];

        return [
            'technical_admin' => [
                'name' => ['fr' => 'Super administrateur technique', 'en' => 'Technical super administrator'],
                'description' => ['fr' => 'Installe et maintient la plateforme. N\'a aucun accès au contenu des signalements.', 'en' => 'Installs and maintains the platform. Has no access to report content.'],
                'permissions' => [Permission::ManageUsers, Permission::ManageSettings, Permission::ViewAuditLog],
            ],
            'organization_admin' => [
                'name' => ['fr' => 'Administrateur d\'organisation', 'en' => 'Organization administrator'],
                'description' => ['fr' => 'Configure l\'organisation, les utilisateurs et les rôles. Pas d\'accès aux dossiers par défaut.', 'en' => 'Configures the organization, users and roles. No case access by default.'],
                'permissions' => [Permission::ManageSettings, Permission::ManageUsers, Permission::ViewAuditLog, Permission::ViewAnalytics],
            ],
            'case_manager' => [
                'name' => ['fr' => 'Gestionnaire de dossiers', 'en' => 'Case manager'],
                'description' => ['fr' => 'Traite les dossiers qui lui sont affectés et échange avec la personne qui signale.', 'en' => 'Handles assigned cases and exchanges with the reporting person.'],
                'permissions' => $caseManager,
            ],
            'supervisor' => [
                'name' => ['fr' => 'Responsable ou superviseur', 'en' => 'Supervisor'],
                'description' => ['fr' => 'Affecte les dossiers, suit l\'activité et consulte le journal d\'audit.', 'en' => 'Assigns cases, follows activity and reads the audit log.'],
                'permissions' => [
                    ...$caseManager,
                    Permission::ViewUnassignedCases,
                    Permission::AssignCases,
                    Permission::ReassignCases,
                    Permission::ViewAnalytics,
                    Permission::ViewAuditLog,
                ],
            ],
            'external_third_party' => [
                'name' => ['fr' => 'Tiers externe indépendant', 'en' => 'Independent external third party'],
                'description' => ['fr' => 'Intervient sur les dossiers qui lui sont transférés, sans accès aux identités.', 'en' => 'Works on transferred cases, without access to identities.'],
                'permissions' => [
                    Permission::ViewAssignedCases,
                    Permission::ViewEvidence,
                    Permission::ReplyToMessages,
                    Permission::CreateInternalNotes,
                    Permission::UpdateCaseStatus,
                ],
            ],
            'auditor' => [
                'name' => ['fr' => 'Auditeur', 'en' => 'Auditor'],
                'description' => ['fr' => 'Consulte le journal d\'audit et les statistiques agrégées.', 'en' => 'Reads the audit log and aggregated statistics.'],
                'permissions' => [Permission::ViewAuditLog, Permission::ViewAnalytics],
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::matrix() as $key => $definition) {
            if (Role::query()->where('key', $key)->exists()) {
                continue;
            }

            $role = Role::query()->create([
                'key' => $key,
                'name' => $definition['name'],
                'description' => $definition['description'],
                'is_system' => true,
            ]);

            $role->grant(...$definition['permissions']);
        }
    }
}
