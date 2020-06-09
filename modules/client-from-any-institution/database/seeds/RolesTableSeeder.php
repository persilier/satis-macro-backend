<?php

namespace Satis2020\ClientFromAnyInstitution\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        // create staff from any unit permissions
        $permission_list = Permission::create(['name' => 'list-client-from-any-institution', 'guard_name' => 'api']);
        $permission_store = Permission::create(['name' => 'store-client-from-any-institution', 'guard_name' => 'api']);
        $permission_update = Permission::create(['name' => 'update-client-from-any-institution', 'guard_name' => 'api']);
        $permission_destroy = Permission::create(['name' => 'destroy-client-from-any-institution', 'guard_name' => 'api']);
        $permission_show = Permission::create(['name' => 'show-client-from-any-institution', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // get role admin
            $role_admin = Role::where('name', 'admin')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin->givePermissionTo([
                $permission_list, $permission_store, $permission_update, $permission_destroy, $permission_show
            ]);

        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin_holding = Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_holding->givePermissionTo([
                $permission_list, $permission_store, $permission_update, $permission_destroy, $permission_show
            ]);

        }

        if ($nature === 'HUB') {

        }

        if ($nature === 'PRO') {
            $role_admin_pro = Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_pro->givePermissionTo([
                $permission_list, $permission_store, $permission_update, $permission_destroy, $permission_show
            ]);

        }

    }
}
