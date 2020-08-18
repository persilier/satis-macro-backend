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

class PurifyRolesPermissionsHoldingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $holdingRoles = [
            "admin-holding" => [
                'list-any-institution', 'store-any-institution', 'update-any-institution', 'destroy-any-institution', 'show-any-institution',
                'list-category-client', 'store-category-client', 'update-category-client', 'destroy-category-client', 'show-category-client',
                'list-channel', 'store-channel', 'update-channel', 'destroy-channel', 'show-channel',
                'list-claim-category', 'store-claim-category', 'update-claim-category', 'destroy-claim-category', 'show-claim-category',
                'list-claim-object', 'store-claim-object', 'update-claim-object', 'destroy-claim-object', 'show-claim-object',
                'update-claim-object-requirement',
                'list-client-from-any-institution', 'store-client-from-any-institution', 'update-client-from-any-institution', 'destroy-client-from-any-institution', 'show-client-from-any-institution',
                'show-mail-parameters', 'update-mail-parameters',
                'show-sms-parameters', 'update-sms-parameters',
                'list-currency', 'store-currency', 'update-currency', 'destroy-currency', 'show-currency',
                'show-dashboard-data-all-institution',
                'list-faq-category', 'store-faq-category', 'update-faq-category', 'destroy-faq-category', 'show-faq-category',
                'list-message-apis', 'store-message-apis', 'update-message-apis', 'destroy-message-apis', 'update-institution-message-api', 'update-my-institution-message-api',
                'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position',
                'list-unit-type', 'store-unit-type', 'update-unit-type', 'destroy-unit-type', 'show-unit-type',
                'list-any-unit', 'store-any-unit', 'update-any-unit', 'destroy-any-unit', 'show-any-unit',
                'list-category-client-from-my-institution', 'store-category-client-from-my-institution', 'update-category-client-from-my-institution', 'destroy-category-client-from-my-institution', 'show-category-client-from-my-institution',
                'update-notifications',
                'list-performance-indicator', 'store-performance-indicator', 'update-performance-indicator', 'destroy-performance-indicator', 'show-performance-indicator', 'edit-performance-indicator',
                'update-processiong-circuit-any-institution',
                'list-severity-level', 'store-severity-level', 'update-severity-level', 'destroy-severity-level', 'show-severity-level',
                'list-staff-from-any-unit', 'store-staff-from-any-unit', 'update-staff-from-any-unit', 'destroy-staff-from-any-unit', 'show-staff-from-any-unit', 'edit-staff-from-any-unit',
            ],
            "pilot-holding" => [
                'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
                'store-claim-against-any-institution',
                'list-claim-awaiting-validation-my-institution', 'show-claim-awaiting-validation-my-institution', 'validate-treatment-my-institution',
                'list-claim-archived', 'show-claim-archived',
                'list-claim-satisfaction-measured', 'update-claim-satisfaction-measured',
                'show-dashboard-data-all-institution', 'show-dashboard-data-my-institution',
                'list-my-discussions', 'list-discussion-contributors', 'contribute-discussion',
                'list-monitoring-claim-any-institution',
                'list-reporting-claim-any-institution',
                'transfer-claim-to-circuit-unit',
                'transfer-claim-to-targeted-institution',
                'list-claim-incomplete-against-any-institution', 'show-claim-incomplete-against-any-institution', 'update-claim-incomplete-against-any-institution',
            ],
            "supervisor-holding" => [],
            "collector-holding" => [
                'store-claim-against-any-institution',
                'list-claim-satisfaction-measured', 'update-claim-satisfaction-measured',
                'show-dashboard-data-my-activity',
                'list-claim-incomplete-against-any-institution', 'show-claim-incomplete-against-any-institution', 'update-claim-incomplete-against-any-institution',
            ],
            "staff" => [
                'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
                'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
                'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            ]
        ];

        foreach ($holdingRoles as $roleName => $permissions) {

            $role = Role::where('name', $roleName)->where('guard_name', 'api')->first();

            if (is_null($role)) {
                $role = Role::create(['name' => $roleName, 'guard_name' => 'api']);
            }

            // sync permissions
            foreach ($permissions as $permissionName) {
                if (Permission::where('name', $permissionName)->where('guard_name', 'api')->doesntExist()) {
                    Permission::create(['name' => $permissionName, 'guard_name' => 'api']);
                }
            }

            $role->syncPermissions($permissions);
        }

    }
}
