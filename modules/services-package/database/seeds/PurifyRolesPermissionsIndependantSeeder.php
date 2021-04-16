<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Treatment;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PurifyRolesPermissionsIndependantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nature = env('APP_NATURE');
        if ($nature === 'PRO') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            $independantRoles = [
                "admin-pro" => [
                    'list-category-client', 'store-category-client', 'update-category-client', 'destroy-category-client', 'show-category-client',
                    'list-channel', 'store-channel', 'update-channel', 'destroy-channel', 'show-channel',
                    'list-claim-category', 'store-claim-category', 'update-claim-category', 'destroy-claim-category', 'show-claim-category',
                    'list-claim-object', 'store-claim-object', 'update-claim-object', 'destroy-claim-object', 'show-claim-object',
                    'update-claim-object-requirement',
                    'list-client-from-my-institution', 'store-client-from-my-institution', 'update-client-from-my-institution', 'destroy-client-from-my-institution', 'show-client-from-my-institution',
                    'show-mail-parameters', 'update-mail-parameters',
                    'show-sms-parameters', 'update-sms-parameters',
                    'list-currency', 'store-currency', 'update-currency', 'destroy-currency', 'show-currency',
                    'list-faq-category', 'store-faq-category', 'update-faq-category', 'destroy-faq-category', 'show-faq-category',
                    'list-message-apis', 'store-message-apis', 'update-message-apis', 'destroy-message-apis', 'update-my-institution-message-api',
                    'update-my-institution',
                    'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position',
                    'list-unit-type', 'store-unit-type', 'update-unit-type', 'destroy-unit-type', 'show-unit-type',
                    'list-my-unit', 'store-my-unit', 'update-my-unit', 'destroy-my-unit', 'show-my-unit',
                    'list-category-client-from-my-institution', 'store-category-client-from-my-institution', 'update-category-client-from-my-institution', 'destroy-category-client-from-my-institution', 'show-category-client-from-my-institution',
                    'update-notifications',
                    'list-performance-indicator', 'store-performance-indicator', 'update-performance-indicator', 'destroy-performance-indicator', 'show-performance-indicator', 'edit-performance-indicator',
                    'update-processing-circuit-my-institution',
                    'list-severity-level', 'update-severity-level', 'show-severity-level',
                    'list-staff-from-my-unit', 'store-staff-from-my-unit', 'update-staff-from-my-unit', 'destroy-staff-from-my-unit', 'show-staff-from-my-unit',
                    'show-dashboard-data-my-institution',
                    'list-account-type', 'show-account-type', 'update-account-type', 'store-account-type', 'destroy-account-type',
                    'list-user-my-institution', 'show-user-my-institution', 'store-user-my-institution',
                    'list-delai-qualification-parameters', 'show-delai-qualification-parameters', 'store-delai-qualification-parameters', 'destroy-delai-qualification-parameters',
                    'list-delai-treatment-parameters', 'show-delai-treatment-parameters', 'store-delai-treatment-parameters', 'destroy-delai-treatment-parameters',
                    'update-components-parameters',
                    'list-my-institution-type-role', 'show-my-institution-type-role', 'store-my-institution-type-role', 'update-my-institution-type-role', 'destroy-my-institution-type-role',
                    'update-active-pilot',
                    'update-recurrence-alert-settings',
                    'update-reject-unit-transfer-parameters',
                    'update-min-fusion-percent-parameters',
                    'update-relance-parameters',
                    'list-faq', 'show-faq','store-faq', 'update-faq', 'delete-faq',
                    'search-claim-my-reference',
                ],
                "pilot" => [
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
                ],
                "supervisor-pro" => [],
                "collector-filial-pro" => [
                    'store-claim-against-my-institution',
                    'list-satisfaction-measured-my-claim', 'update-satisfaction-measured-my-claim',
                    'list-claim-incomplete-against-my-institution', 'show-claim-incomplete-against-my-institution', 'update-claim-incomplete-against-my-institution',
                    'show-dashboard-data-my-activity',
                    'history-list-create-claim',
                    'search-claim-my-reference',
                    'attach-files-to-claim',
                    'revive-staff',
                ],
                "staff" => [
                    'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
                    'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
                    'show-dashboard-data-my-unit',
                    'show-dashboard-data-my-activity',
                    'history-list-treat-claim',
                    'search-claim-my-reference',
                    'attach-files-to-claim'
                ]
            ];

            foreach ($independantRoles as $roleName => $permissions) {

                $role = Role::where('name', $roleName)->where('guard_name', 'api')->first();

                if (is_null($role)) {
                    $role = Role::create(['name' => $roleName, 'guard_name' => 'api']);
                }

                if (empty($permissions)) {
                    $role->syncPermissions($permissions);
                    $role->forceDelete();
                }

                // sync permissions
                foreach ($permissions as $permissionName) {
                    if (Permission::where('name', $permissionName)->where('guard_name', 'api')->doesntExist()) {
                        Permission::create(['name' => $permissionName, 'guard_name' => 'api']);
                    }
                }

                $role->syncPermissions($permissions);
                $role->update(['is_editable' => 0]);
            }

            Permission::doesntHave('roles')->delete();

        }
    }
}
