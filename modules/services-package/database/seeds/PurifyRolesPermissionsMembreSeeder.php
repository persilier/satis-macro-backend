<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PurifyRolesPermissionsMembreSeeder extends Seeder
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
        if ($nature === 'HUB') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            $memberRoles = [
                "supervisor-membre" => [],
                "staff" => [
                    'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
                    'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
                    'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
                    'history-list-treat-claim',
                    'history-list-treat-claim',
                    'list-staff-revivals','list-unit-revivals',
                    'history-list-treat-claim','show-my-staff-monitoring',
                    'revive-staff',
                ]
            ];


            foreach ($memberRoles as $roleName => $permissions) {

                $institutionTypes = $this->addInstitutionTypeToRole($roleName, 'membre');

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

        }
    }
}
