<?php

namespace Satis2020\StaffFromAnyUnit\Database\Seeds;

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

        // create staff from any unit permissions
        $permission_list = Permission::create(['name' => 'list-staff-from-any-unit', 'guard_name' => 'api']);
        $permission_store = Permission::create(['name' => 'store-staff-from-any-unit', 'guard_name' => 'api']);
        $permission_update = Permission::create(['name' => 'update-staff-from-any-unit', 'guard_name' => 'api']);
        $permission_destroy = Permission::create(['name' => 'destroy-staff-from-any-unit', 'guard_name' => 'api']);
        $permission_show = Permission::create(['name' => 'show-staff-from-any-unit', 'guard_name' => 'api']);
        $permission_edit = Permission::create(['name' => 'edit-staff-from-any-unit', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // get role admin
            $role_admin = Role::where('name', 'admin')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin->givePermissionTo([
                $permission_list, $permission_store, $permission_update, $permission_destroy, $permission_show, $permission_edit
            ]);

        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin_holding = Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_holding->givePermissionTo([
                $permission_list, $permission_store, $permission_update, $permission_destroy, $permission_show, $permission_edit
            ]);

        }

    }
}
