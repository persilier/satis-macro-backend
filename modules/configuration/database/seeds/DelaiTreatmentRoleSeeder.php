<?php

namespace Satis2020\Configuration\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DelaiTreatmentRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nature = env('APP_NATURE');
        // create staff from any unit permissions
        $permission_list = Permission::create(['name' => 'list-delai-treatment-parameters', 'guard_name' => 'api']);
        $permission_show = Permission::create(['name' => 'show-delai-treatment-parameters', 'guard_name' => 'api']);
        $permission_store = Permission::create(['name' => 'store-delai-treatment-parameters', 'guard_name' => 'api']);
        $permission_destroy= Permission::create(['name' => 'destroy-delai-treatment-parameters', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // get role admin
            $role_admin = Role::where('name', 'admin')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin->givePermissionTo([
                $permission_list, $permission_show, $permission_store, $permission_destroy
            ]);

        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin = Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin->givePermissionTo([
                $permission_list, $permission_show, $permission_store, $permission_destroy
            ]);

        }

        if ($nature == 'HUB') {
            // retrieve admin roles
            $role_admin_observatory = Role::where('name', 'admin-observatory')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_observatory->givePermissionTo([
                $permission_list, $permission_show, $permission_store, $permission_destroy
            ]);
        }

        if ($nature == 'PRO') {
            $role_admin_pro = Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_pro->givePermissionTo([
                $permission_list, $permission_show, $permission_store, $permission_destroy
            ]);

        }

    }
}
