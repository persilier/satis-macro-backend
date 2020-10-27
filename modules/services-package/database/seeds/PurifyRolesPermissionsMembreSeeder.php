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

class PurifyRolesPermissionsMembreSeeder extends Seeder
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

            $memberRoles = [
                "supervisor-membre" => [],
                "staff" => [
                    'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
                    'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
                    'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
                    'history-list-treat-claim',
                ]
            ];


            foreach ($memberRoles as $roleName => $permissions) {

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
            }

        }
    }
}
