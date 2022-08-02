<?php

namespace Satis2020\ClaimAwaitingAssignment\Database\Seeds;

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
        $permission_list = Permission::create(['name' => 'list-claim-awaiting-assignment', 'guard_name' => 'api']);
        $permission_show = Permission::create(['name' => 'show-claim-awaiting-assignment', 'guard_name' => 'api']);
        $permission_merge = Permission::create(['name' => 'merge-claim-awaiting-assignment', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // create admin roles
            $role_pilot = Role::create(['name' => 'pilot', 'guard_name' => 'api']);

            $role_pilot->givePermissionTo([
                $permission_list, $permission_show, $permission_merge
            ]);

            User::find('8df01ee3-7f66-4328-9510-f75666f4bc4a')->assignRole($role_pilot);

        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_pilot_holding = Role::create(['name' => 'pilot-holding', 'guard_name' => 'api']);
            $role_pilot_filial = Role::create(['name' => 'pilot-filial', 'guard_name' => 'api']);

            // associate permissions to roles
            $role_pilot_holding->givePermissionTo([
                $permission_list, $permission_show, $permission_merge
            ]);

            $role_pilot_filial->givePermissionTo([
                $permission_list, $permission_show, $permission_merge
            ]);

            // associate roles to admin holding
            User::find('6f53d239-2890-4faf-9af9-f5a97aee881e')->assignRole($role_pilot_holding);
            User::find('ceefcca8-35c6-4e62-9809-42bf6b9adb20')->assignRole($role_pilot_filial);

        }

        if ($nature == 'HUB') {
            $role_pilot = Role::create(['name' => 'pilot', 'guard_name' => 'api']);

            $role_pilot->givePermissionTo([
                $permission_list, $permission_show, $permission_merge
            ]);

            // associate roles to admin observatory
            User::find('94656cd3-d0c7-45bb-83b6-5ded02ded07b')->assignRole($role_pilot);
        }

        if ($nature == 'PRO') {
            $role_pilot = Role::create(['name' => 'pilot', 'guard_name' => 'api']);

            $role_pilot->givePermissionTo([
                $permission_list, $permission_show, $permission_merge
            ]);

            // associate roles to admin pro
            User::find('18732c5e-b485-474e-811d-de9bbb8d6cf2')->assignRole($role_pilot);
            
        }

    }
}
