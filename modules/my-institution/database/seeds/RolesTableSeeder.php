<?php

namespace Satis2020\MyInstitution\Database\Seeds;

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

        $permission_update = Permission::create(['name' => 'update-my-institution', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // get role admin
            $role_admin = Role::where('name', 'admin')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin->givePermissionTo([
                $permission_update
            ]);

        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin_filial = Role::where('name', 'admin-filial')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_filial->givePermissionTo([
                $permission_update
            ]);

        }

        if ($nature === 'HUB') {

        }

        if ($nature === 'PRO') {
            // create admin roles
            $role_admin_pro = Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_pro->givePermissionTo([
                $permission_update
            ]);

        }

    }
}
