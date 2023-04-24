<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;
use Satis2020\ServicePackage\Models\Module;

class PurifyRolesPermissionsFilialSeeder extends Seeder
{

    public function addInstitutionTypeToRole($roleName, $institutionType)
    {
        $role = Role::where("name", $roleName)->where("guard_name", "api")->first();

        if (is_null($role)) {
            return json_encode([$institutionType]);
        }

        $institution_types = is_null($role->institution_types)
            ? []
            : json_decode($role->institution_types);

        if (!in_array($institutionType, $institution_types)) {
            array_push($institution_types, $institutionType);
        }

        return json_encode($institution_types);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nature = Config::get('services.app_nature', 'PRO');
        if ($nature === 'MACRO') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            $filialRoles = [
                "admin-filial" => [
                    'list-client-from-my-institution', 'store-client-from-my-institution', 'update-client-from-my-institution', 'destroy-client-from-my-institution', 'show-client-from-my-institution',
                    'update-my-institution-message-api',
                    'update-my-institution',
                    'list-my-unit', 'store-my-unit', 'update-my-unit', 'destroy-my-unit', 'show-my-unit',
                    'update-processing-circuit-my-institution',
                    'list-staff-from-my-unit', 'store-staff-from-my-unit', 'update-staff-from-my-unit', 'destroy-staff-from-my-unit', 'show-staff-from-my-unit',
                    'show-dashboard-data-my-institution',
                    'list-user-my-institution', 'show-user-my-institution', 'store-user-my-institution',
                    'update-active-pilot',
                    'search-claim-my-reference',
                    "my-email-claim-configuration",
                    'list-notification-proof',
                    'list-webhooks-config', 'store-webhooks-config', 'update-webhooks-config', 'delete-webhooks-config',
                    'config-reporting-claim-my-institution',
                    'list-notification-proof',
                    'list-reporting-titles-configs', 'update-reporting-titles-configs', 'edit-reporting-titles-configs',

                    'bci-monthly-reports', 'bci-annual-reports',
                    'export-notification-proof', 'update-mail-parameters',
                    'list-config-reporting-claim-my-institution', 'store-config-reporting-claim-my-institution', 'update-config-reporting-claim-my-institution', 'delete-config-reporting-claim-my-institution',
                    'show-proxy-config', 'update-proxy-config', 'delete-proxy-config',
                    'list-escalation-config', 'update-escalation-config',
                    'logout-user-my-institution', 'access-satisfaction-data-config',
                    'update-satisfaction-data-config',
                    'configure-pilot-collector-discussion-attribute',
                    'list-auth-config', 'update-auth-config', 'update-relance-parameters', 'list-account-type','update-category-client','internal-control-store','config-reporting-claim-any-institution','activity-log'
                ],
                "pilot-filial" => [
                    'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
                    'store-claim-against-my-institution',
                    'list-claim-awaiting-validation-my-institution', 'show-claim-awaiting-validation-my-institution', 'validate-treatment-my-institution',
                    'list-my-claim-archived', 'show-my-claim-archived',
                    'list-satisfaction-measured-my-claim', 'update-satisfaction-measured-my-claim',
                    'list-my-discussions', 'list-discussion-contributors', 'contribute-discussion',
                    'list-monitoring-claim-my-institution',
                    'list-reporting-claim-my-institution',
                    'transfer-claim-to-circuit-unit',
                    'list-claim-incomplete-against-my-institution', 'show-claim-incomplete-against-my-institution', 'update-claim-incomplete-against-my-institution',
                    'show-dashboard-data-my-institution',
                    'history-list-create-claim',
                    'update-active-pilot',
                    'unfounded-claim-awaiting-assignment',
                    'search-claim-my-reference',
                    'attach-files-to-claim',
                    'revive-staff',
                    'pilot-list-notification-proof',
                    'list-webhooks-config', 'store-webhooks-config', 'update-webhooks-config', 'delete-webhooks-config',
                    'list-regulatory-reporting-claim-my-institution',
                    'list-system-usage-reporting', 'list-benchmarking-reporting', 'list-global-reporting',
                    'system-any-efficiency-report',
                    'list-reporting-titles-configs', 'update-reporting-titles-configs', 'edit-reporting-titles-configs',
                    'bci-monthly-reports', 'bci-annual-reports',
                    'pilot-export-notification-proof',
                    'list-my-claim-unsatisfied',
                    
                    
                    'list-closed-my-claims', 'close-my-claims',
                    'list-escalation-config', 'update-escalation-config',
                    'list-treatment-board', 'store-treatment-board', 'update-treatment-board',
                    'destroy-treatment-board',
                    'list-claim-transferred-my-institution', 'access-satisfaction-data-config','staff-list-satisfaction-measured-my-claim',
                    'auto-affect-claim-for-satisfaction-collector',
                    'show-my-pilot-monitoring',
                    'show-my-pilotUnit-monitoring', 'update-my-institution',
                    'system-my-efficiency-report',
                    'show-my-collector-monitoring',
                ],
                "supervisor-filial" => [],
                "collector-filial-pro" => [
                    'store-claim-against-my-institution',
                    'list-satisfaction-measured-my-claim', 'update-satisfaction-measured-my-claim',
                    'list-claim-incomplete-against-my-institution', 'show-claim-incomplete-against-my-institution', 'update-claim-incomplete-against-my-institution',
                    'show-dashboard-data-my-activity',
                    'history-list-create-claim',
                    'search-claim-my-reference',
                    'attach-files-to-claim',
                    'revive-staff', 'access-satisfaction-data-config','staff-list-satisfaction-measured-my-claim'
                ],
                "staff" => [
                    'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
                    'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
                    'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
                    'history-list-treat-claim',
                    'search-claim-my-reference',
                    'attach-files-to-claim',
                    'show-my-staff-monitoring',
                    'list-staff-revivals', 'list-unit-revivals',
                    'revive-staff', 'access-satisfaction-data-config'
                ],
                'satisfaction-mesure' => [
                    'staff-list-satisfaction-measured-my-claim', 'update-satisfaction-measured-my-claim', 'access-satisfaction-data-config'
                ],
                'internal-controller' => [
                    "internal-control-claim", 'internal-control-index', 'internal-control-claim-detail', 'internal-control-claim-object-index', 'access-satisfaction-data-config'
                ],
                'conformer' => [],
                'staff-service-quality' => [],
            ];

            foreach ($filialRoles as $roleName => $permissions) {

                $institutionTypes = $this->addInstitutionTypeToRole($roleName, 'filiale');

                $role = Role::updateOrCreate(
                    ['name' => $roleName, 'guard_name' => 'api'],
                    ['institution_types' => $institutionTypes]
                );

                if (empty($permissions)) {
                    $role->syncPermissions($permissions);
                    $role->forceDelete();
                } else {
                    // sync permissions
                    foreach ($permissions as $permissionName) {
                        if (Permission::where('name', $permissionName)->where('guard_name', 'api')->doesntExist()) {
                            Permission::create(['name' => $permissionName, 'guard_name' => 'api']);
                        }
                    }

                    $role->syncPermissions($permissions);
                    $role->update(['is_editable' => 0]);
                }
            }

            Permission::doesntHave('roles')->delete();

            $modules = [
                "Collecte filiale" => "collector-filial-pro",
                "Traitement filiale" =>  "staff",
                "Pilotage du processus filiale" => "pilot-filial",
                "Administration filiale" =>  "admin-filial",
                "Contrôle interne filiale" =>  "supervisor-filial",
            ];

            $permissionsAssociatedToModules = collect([]);

            foreach ($modules as $moduleName => $roleName) {
                // CreateOrUpdate $module
                $module = Module::updateOrCreate(
                    ['name->' . app()->getLocale() => $moduleName],
                    ["name" => $moduleName, "description" => $moduleName]
                );

                $modulePermissions = $filialRoles[$roleName];

                foreach ($modulePermissions as $permissionName) {
                    // verify if permission already have module
                    if ($permissionsAssociatedToModules->search($permissionName) === false) {
                        // Associate permission to module
                        Permission::where('guard_name', 'api')->where('name', $permissionName)->update(['module_id' => $module->id]);
                        // Add permission to permissionsAssociatedToModules
                        $permissionsAssociatedToModules->push($permissionName);
                    }
                }
            }
        }
    }
}
