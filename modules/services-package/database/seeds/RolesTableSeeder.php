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

        // create units type permissions
        Permission::create(['name' => 'list-unit-type', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-unit-type', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-unit-type', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-unit-type', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-unit-type', 'guard_name' => 'api']);

        // create units permissions for any institutions
        Permission::create(['name' => 'list-any-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-any-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-any-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-any-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-any-unit', 'guard_name' => 'api']);

        // create units permissions for my institutions
        Permission::create(['name' => 'list-my-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-my-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-my-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-my-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-my-unit', 'guard_name' => 'api']);

        // create units permissions for without link any institutions
        Permission::create(['name' => 'list-without-link-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-without-link-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-without-link-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-without-link-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-without-link-unit', 'guard_name' => 'api']);

        // create types client-from-my-institution permissions
        Permission::create(['name' => 'list-type-client-from-my-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-type-client-from-my-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-type-client-from-my-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-type-client-from-my-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-type-client-from-my-institution', 'guard_name' => 'api']);

        // create categories client-from-my-institution permissions
        Permission::create(['name' => 'list-category-client-from-my-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-category-client-from-my-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-category-client-from-my-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-category-client-from-my-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-category-client-from-my-institution', 'guard_name' => 'api']);

        // create institutions permissions
        Permission::create(['name' => 'list-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-institution', 'guard_name' => 'api']);

        if ($nature == 'MACRO') {
            // create admin roles
            $role_admin_holding = Role::create(['name' => 'admin-holding', 'guard_name' => 'api']);
            $role_admin_filial = Role::create(['name' => 'admin-filial', 'guard_name' => 'api']);

            // associate permissions to roles
            $role_admin_holding->syncPermissions([
                'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position',
                'list-unit-type', 'store-unit-type', 'update-unit-type', 'destroy-unit-type', 'show-unit-type',
                'list-any-unit', 'store-any-unit', 'update-any-unit', 'destroy-any-unit', 'show-any-unit',
                'list-category-client-from-my-institution', 'store-category-client-from-my-institution', 'update-category-client-from-my-institution', 'destroy-category-client-from-my-institution', 'show-category-client-from-my-institution',
                'list-type-client-from-my-institution', 'store-type-client-from-my-institution', 'update-type-client-from-my-institution', 'destroy-type-client-from-my-institution', 'show-type-client-from-my-institution',
                'list-institution', 'store-institution', 'update-institution', 'destroy-institution', 'show-institution',
            ]);

            $role_admin_filial->syncPermissions([
                'list-my-unit', 'store-my-unit', 'update-my-unit', 'destroy-my-unit', 'show-my-unit',
                'list-institution', 'store-institution', 'update-institution', 'destroy-institution', 'show-institution',

            ]);

            // associate roles to admin holding
            User::find('6f53d239-2890-4faf-9af9-f5a97aee881e')->assignRole($role_admin_holding);
            User::find('ceefcca8-35c6-4e62-9809-42bf6b9adb20')->assignRole($role_admin_filial);
            // associate roles to admin filial
        }

        if ($nature == 'HUB') {
            $role_admin_observatory = Role::create(['name' => 'admin-observatory', 'guard_name' => 'api']);

            // associate permissions to roles
            $role_admin_observatory->syncPermissions([
                'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position',
                'list-unit-type', 'store-unit-type', 'update-unit-type', 'destroy-unit-type', 'show-unit-type',
                'list-without-link-unit', 'store-without-link-unit', 'update-without-link-unit', 'destroy-without-link-unit', 'show-without-link-unit',
            ]);

            // associate roles to admin observatory
            User::find('94656cd3-d0c7-45bb-83b6-5ded02ded07b')->assignRole($role_admin_observatory);
        }

        if ($nature == 'PRO') {
            $role_admin_pro = Role::create(['name' => 'admin-pro', 'guard_name' => 'api']);

            // associate permissions to roles
            $role_admin_pro->syncPermissions([
                'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position',
                'list-unit-type', 'store-unit-type', 'update-unit-type', 'destroy-unit-type', 'show-unit-type',
                'list-my-unit', 'store-my-unit', 'update-my-unit', 'destroy-my-unit', 'show-my-unit',
                'list-category-client-from-my-institution', 'store-category-client-from-my-institution', 'update-category-client-from-my-institution', 'destroy-category-client-from-my-institution', 'show-category-client-from-my-institution',
                'list-type-client-from-my-institution', 'store-type-client-from-my-institution', 'update-type-client-from-my-institution', 'destroy-type-client-from-my-institution', 'show-type-client-from-my-institution',
            ]);

            // associate roles to admin pro
            User::find('18732c5e-b485-474e-811d-de9bbb8d6cf2')->assignRole($role_admin_pro);
        }

    }
}
