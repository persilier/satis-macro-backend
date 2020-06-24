<?php

namespace Satis2020\ClaimAwaitingAssignment\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Models\Identite;
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
        $permission_list = Permission::create(['name' => 'list-claim-awaiting-treatment', 'guard_name' => 'api']);
        $permission_show = Permission::create(['name' => 'show-claim-awaiting-treatment', 'guard_name' => 'api']);
        $permission_rejected = Permission::create(['name' => 'rejected-claim-awaiting-treatment', 'guard_name' => 'api']);
        $permission_self_assignment = Permission::create(['name' => 'self-assignment-claim-awaiting-treatment', 'guard_name' => 'api']);
        $permission_assignment = Permission::create(['name' => 'assignment-claim-awaiting-treatment', 'guard_name' => 'api']);
        $permission_unfounded= Permission::create(['name' => 'unfounded-claim-awaiting-treatment', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // create admin roles
            $role_staff_lead = Role::create(['name' => 'staff_lead', 'guard_name' => 'api']);

            $role_staff_lead->givePermissionTo([
                $permission_list, $permission_show, $permission_rejected, $permission_self_assignment,$permission_unfounded, $permission_assignment
            ]);

            // create admin roles
            $role_staff = Role::create(['name' => 'staff', 'guard_name' => 'api']);

            $role_staff->givePermissionTo([
                $permission_list, $permission_show, $permission_rejected, $permission_self_assignment,$permission_unfounded
            ]);

            User::find('8df01ee3-7f66-4328-9510-f75666f4bc4a')->assignRole($role_staff_lead);
            User::find('8df01ee3-7f66-4328-9510-f75666f4bc4a')->assignRole($role_staff);

        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_staff_holding = Role::create(['name' => 'staff-holding', 'guard_name' => 'api']);
            $role_staff_filial = Role::create(['name' => 'staff-filial', 'guard_name' => 'api']);

            // associate permissions to roles
            $role_staff_holding->givePermissionTo([
                $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_unfounded
            ]);

            $role_staff_filial->givePermissionTo([
                $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_unfounded
            ]);

            $role_staff_lead_holding = Role::create(['name' => 'staff-lead-holding', 'guard_name' => 'api']);
            $role_staff_lead_filial = Role::create(['name' => 'staff-lead-filial', 'guard_name' => 'api']);

            // associate permissions to roles
            $role_staff_lead_holding->givePermissionTo([
                $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_unfounded, $permission_assignment
            ]);

            $role_staff_lead_filial->givePermissionTo([
                $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_unfounded, $permission_assignment
            ]);


            User::find('6f53d239-2890-4faf-9af9-f5a97aee881e')->assignRole($role_staff_holding);
            User::find('ceefcca8-35c6-4e62-9809-42bf6b9adb20')->assignRole($role_staff_filial);
            User::find('6f53d239-2890-4faf-9af9-f5a97aee881e')->assignRole($role_staff_lead_holding);
            User::find('ceefcca8-35c6-4e62-9809-42bf6b9adb20')->assignRole($role_staff_lead_filial);

        }

        if ($nature == 'HUB') {

        }

        if ($nature == 'PRO') {

            
        }

    }
}
