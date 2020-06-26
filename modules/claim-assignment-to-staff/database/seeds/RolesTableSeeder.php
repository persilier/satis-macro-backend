<?php

namespace Satis2020\ClaimAssignmentToStaff\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Models\Identite;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Role::flushEventListeners();

        $nature = env('APP_NATURE');

        // create permissions
        $permission_list = Permission::create(['name' => 'list-claim-assignment-to-staff', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // create admin roles
            $role_staff_lead = Role::create(['name' => 'staff_lead', 'guard_name' => 'api']);

            $role_staff_lead->givePermissionTo([
                $permission_list
            ]);

            // create admin roles
            $role_staff = Role::create(['name' => 'staff', 'guard_name' => 'api']);

            $role_staff->givePermissionTo([
                $permission_list
            ]);

        }

        if ($nature === 'MACRO') {

            {
                // create admin roles
                $role_staff_holding = Role::create(['name' => 'staff-holding', 'guard_name' => 'api']);
                $role_staff_filial = Role::create(['name' => 'staff-filial', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_holding->givePermissionTo([
                    $permission_list
                ]);

                $role_staff_filial->givePermissionTo([
                    $permission_list
                ]);

            }

            {
                $role_staff_lead_holding = Role::create(['name' => 'staff-lead-holding', 'guard_name' => 'api']);
                $role_staff_lead_filial = Role::create(['name' => 'staff-lead-filial', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_lead_holding->givePermissionTo([
                    $permission_list
                ]);

                $role_staff_lead_filial->givePermissionTo([
                    $permission_list
                ]);

            }


        }

        if ($nature === 'PRO') {

            {
                $role_staff_pro = Role::create(['name' => 'staff-pro', 'guard_name' => 'api']);
                $role_staff_lead_pro = Role::create(['name' => 'staff-lead-pro', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_pro->givePermissionTo([
                    $permission_list]);

                $role_staff_lead_pro->givePermissionTo([
                    $permission_list
                ]);

            }
        }

        if ($nature === 'HUB') {

            {
                // create admin roles
                $role_staff_observatory = Role::create(['name' => 'staff-holding', 'guard_name' => 'api']);
                $role_staff_membre = Role::create(['name' => 'staff-membre', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_observatory->givePermissionTo([
                    $permission_list
                ]);

                $role_staff_membre->givePermissionTo([
                    $permission_list
                ]);

            }

            {
                $role_staff_lead_observatory = Role::create(['name' => 'staff-lead-observatory', 'guard_name' => 'api']);
                $role_staff_lead_membre = Role::create(['name' => 'staff-lead-membre', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_lead_observatory->givePermissionTo([
                    $permission_list
                ]);

                $role_staff_lead_membre->givePermissionTo([
                    $permission_list
                ]);

            }
        }

    }
}
