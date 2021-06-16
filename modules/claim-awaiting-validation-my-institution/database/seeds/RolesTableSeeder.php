<?php

namespace Satis2020\ClaimAwaitingValidationMyInstitution\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Role::flushEventListeners();

        $nature = env('APP_NATURE');

        // create permissions
        $permission_show = Permission::create(['name' => 'show-claim-awaiting-validation-my-institution', 'guard_name' => 'api']);
        $permission_validate = Permission::create(['name' => 'validate-treatment-my-institution', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // create admin roles
            $role_pilot = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            $role_pilot->givePermissionTo([
                $permission_show, $permission_validate
            ]);
        }

        if ($nature === 'MACRO') {
            $permission_list = Permission::create(['name' => 'list-claim-awaiting-validation-my-institution', 'guard_name' => 'api']);

            // create admin roles
            $role_pilot_holding = Role::where('name', 'pilot-holding')->where('guard_name', 'api')->firstOrFail();
            $role_pilot_filial = Role::where('name', 'pilot-filial')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_pilot_holding->givePermissionTo([
                $permission_list, $permission_show, $permission_validate
            ]);

            $role_pilot_filial->givePermissionTo([
                $permission_list, $permission_show, $permission_validate
            ]);
        }

        if ($nature == 'PRO') {
            $permission_list = Permission::create(['name' => 'list-claim-awaiting-validation-my-institution', 'guard_name' => 'api']);

            $role_pilot = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            $role_pilot->givePermissionTo([
                $permission_list, $permission_show, $permission_validate
            ]);
        }

    }
}
