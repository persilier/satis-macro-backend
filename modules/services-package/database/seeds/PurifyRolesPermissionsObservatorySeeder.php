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

class PurifyRolesPermissionsObservatorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nature = env('APP_NATURE');
        if ($nature === 'HUB') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            $observatoryRoles = [
                "admin-observatory" => [
                    'list-category-client', 'store-category-client', 'update-category-client', 'destroy-category-client', 'show-category-client',
                    'list-channel', 'store-channel', 'update-channel', 'destroy-channel', 'show-channel',
                    'list-claim-category', 'store-claim-category', 'update-claim-category', 'destroy-claim-category', 'show-claim-category',
                    'list-claim-object', 'store-claim-object', 'update-claim-object', 'destroy-claim-object', 'show-claim-object',
                    'update-claim-object-requirement',
                    'show-mail-parameters', 'update-mail-parameters',
                    'show-sms-parameters', 'update-sms-parameters',
                    'list-currency', 'store-currency', 'update-currency', 'destroy-currency', 'show-currency',
                    'list-faq-category', 'store-faq-category', 'update-faq-category', 'destroy-faq-category', 'show-faq-category',
                    'list-message-apis', 'store-message-apis', 'update-message-apis', 'destroy-message-apis', 'update-my-institution-message-api',
                    'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position',
                    'list-unit-type', 'store-unit-type', 'update-unit-type', 'destroy-unit-type', 'show-unit-type',
                    'list-without-link-unit', 'store-without-link-unit', 'update-without-link-unit', 'destroy-without-link-unit', 'show-without-link-unit',
                    'update-notifications',
                    'list-performance-indicator', 'store-performance-indicator', 'update-performance-indicator', 'destroy-performance-indicator', 'show-performance-indicator', 'edit-performance-indicator',
                    'update-processiong-circuit-without-institution',
                    'list-relationship', 'store-relationship', 'update-relationship', 'destroy-relationship', 'show-relationship',
                    'list-severity-level', 'store-severity-level', 'update-severity-level', 'destroy-severity-level', 'show-severity-level',
                    'list-staff-from-maybe-no-unit', 'store-staff-from-maybe-no-unit', 'update-staff-from-maybe-no-unit', 'destroy-staff-from-maybe-no-unit', 'show-staff-from-maybe-no-unit',
                    'show-dashboard-data-all-institution',
                    'list-account-type', 'show-account-type', 'update-account-type', 'store-account-type', 'destroy-account-type',
                    'list-user-any-institution', 'show-user-any-institution', 'store-user-any-institution',
                    'list-delai-qualification-parameters', 'show-delai-qualification-parameters', 'store-delai-qualification-parameters', 'destroy-delai-qualification-parameters',
                    'list-delai-treatment-parameters', 'show-delai-treatment-parameters', 'store-delai-treatment-parameters', 'destroy-delai-treatment-parameters',
                    'update-components-parameters',
                    'list-any-institution-type-role', 'show-any-institution-type-role', 'store-any-institution-type-role', 'update-any-institution-type-role', 'destroy-any-institution-type-role',
                    'update-active-pilot',
                    'update-recurrence-alert-settings',
                    'update-reject-unit-transfer-parameters',
                    'update-min-fusion-percent-parameters',
                    'update-relance-parameters',
                    'list-faq',
                    'search-claim-any-reference',
                ],
                "pilot" => [
                    'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
                    'store-claim-without-client',
                    'list-claim-awaiting-validation-any-institution', 'show-claim-awaiting-validation-any-institution', 'validate-treatment-any-institution',
                    'list-any-claim-archived', 'show-any-claim-archived',
                    'list-satisfaction-measured-any-claim', 'update-satisfaction-measured-any-claim',
                    'list-my-discussions', 'list-discussion-contributors', 'contribute-discussion',
                    'list-monitoring-claim-any-institution',
                    'list-reporting-claim-any-institution',
                    'transfer-claim-to-unit',
                    'list-claim-incomplete-without-client', 'show-claim-incomplete-without-client', 'update-claim-incomplete-without-client',
                    'show-dashboard-data-all-institution',
                    'history-list-create-claim',
                    'update-active-pilot',
                    'unfounded-claim-awaiting-assignment',
                    'search-claim-any-reference',
                ],
                "supervisor-observatory" => [],
                "collector-observatory" => [
                    'store-claim-without-client',
                    'list-satisfaction-measured-any-claim', 'update-satisfaction-measured-any-claim',
                    'list-claim-incomplete-without-client', 'show-claim-incomplete-without-client', 'update-claim-incomplete-without-client',
                    'show-dashboard-data-my-activity',
                    'history-list-create-claim',
                    'search-claim-any-reference',
                ],
                "staff" => [
                    'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
                    'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
                    'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
                    'history-list-treat-claim',
                    'search-claim-any-reference',
                ]
            ];

            foreach ($observatoryRoles as $roleName => $permissions) {

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

        }
    }
}
