<?php

namespace Satis2020\ServicePackage\Database\Seeds;

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
        DB::table('roles')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        Role::flushEventListeners();

        $nature = env('APP_NATURE');

        // create positions permissions
        Permission::create(['name' => 'list-position', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-position', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-position', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-position', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-position', 'guard_name' => 'api']);

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin_holding = Role::create(['name' => 'admin-holding', 'guard_name' => 'api']);
            $role_admin_filial = Role::create(['name' => 'admin-filial', 'guard_name' => 'api']);

            // associate permissions to roles
            $role_admin_holding->syncPermissions([
                'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position'
            ]);

            $role_admin_filial->syncPermissions([

            ]);

            // associate roles to admin holding
            User::find('6f53d239-2890-4faf-9af9-f5a97aee881e')->assignRole($role_admin_holding);

            // associate roles to admin filial
        }

        if ($nature === 'HUB') {
            $role_admin_observatory = Role::create(['name' => 'admin-observatory', 'guard_name' => 'api']);

            // associate permissions to roles
            $role_admin_observatory->syncPermissions([
                'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position'
            ]);

            // associate roles to admin observatory
            User::find('94656cd3-d0c7-45bb-83b6-5ded02ded07b')->assignRole($role_admin_observatory);
        }

        if ($nature === 'PRO') {
            $role_admin_pro = Role::create(['name' => 'admin-pro', 'guard_name' => 'api']);

            // associate permissions to roles
            $role_admin_pro->syncPermissions([
                'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position'
            ]);

            // associate roles to admin pro
        }

    }
}
