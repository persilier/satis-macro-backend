<?php

namespace Satis2020\ClaimAwaitingTreatment\Database\Seeds;

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
    function assignRolesStaff($institutionId, $role_lead, $role)
    {

        $staffs = Staff::with('identite.user')->whereHas('identite', function ($query) {
            $query->has('user');
        })->where('institution_id', $institutionId)->get();

        // Enregistrement des réclammations
        foreach ($staffs as $staff) {
            $unit = $staff->load('unit')->unit;

            $user = $staff->identite->user;

            if ($unit->lead_id === $staff->id) {

                $user->assignRole($role_lead);

            } else {

                $user->assignRole($role);
            }
        }

        return true;
    }

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
        $permission_assignment_list = Permission::create(['name' => 'list-claim-assignment-to-staff', 'guard_name' => 'api']);
        $permission_assignment_show = Permission::create(['name' => 'show-claim-assignment-to-staff', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // create admin roles
            $role_staff_lead = Role::create(['name' => 'staff_lead', 'guard_name' => 'api']);

            $role_staff_lead->givePermissionTo([
                $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment, $permission_assignment_show
            ]);

            // create admin roles
            $role_staff = Role::create(['name' => 'staff', 'guard_name' => 'api']);

            $role_staff->givePermissionTo([
                $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment_show
            ]);

        }

        if ($nature === 'MACRO') {

            {
                // create admin roles
                $role_staff_holding = Role::create(['name' => 'staff-holding', 'guard_name' => 'api']);
                $role_staff_filial = Role::create(['name' => 'staff-filial', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_holding->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment_show
                ]);

                $role_staff_filial->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment_show
                ]);

            }

            {
                $role_staff_lead_holding = Role::create(['name' => 'staff-lead-holding', 'guard_name' => 'api']);
                $role_staff_lead_filial = Role::create(['name' => 'staff-lead-filial', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_lead_holding->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment, $permission_assignment_show
                ]);

                $role_staff_lead_filial->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment, $permission_assignment_show
                ]);

            }

            {
                $this->assignRolesStaff('3d7f426e-494a-4650-a615-315db1b38c52', $role_staff_lead_holding, $role_staff_holding);
                $this->assignRolesStaff('b99a6d22-4af1-4a8a-9589-81468f5c020b', $role_staff_lead_filial, $role_staff_filial);
            }


        }

        if ($nature === 'PRO') {

            {
                $role_staff_pro = Role::create(['name' => 'staff-pro', 'guard_name' => 'api']);
                $role_staff_lead_pro = Role::create(['name' => 'staff-lead-pro', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_pro->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_assignment_list, $permission_self_assignment, $permission_assignment_show
                ]);

                $role_staff_lead_pro->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment, $permission_assignment_show
                ]);

            }

            {
                $this->assignRolesStaff('43ebf6c0-be03-4881-8196-59d476f75c9e', $role_staff_lead_pro, $role_staff_pro);
            }
        }

        if ($nature === 'HUB') {

            {
                // create admin roles
                $role_staff_observatory = Role::create(['name' => 'staff-holding', 'guard_name' => 'api']);
                $role_staff_membre = Role::create(['name' => 'staff-membre', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_observatory->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment_show
                ]);

                $role_staff_membre->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment_show
                ]);

            }

            {
                $role_staff_lead_observatory = Role::create(['name' => 'staff-lead-observatory', 'guard_name' => 'api']);
                $role_staff_lead_membre = Role::create(['name' => 'staff-lead-membre', 'guard_name' => 'api']);

                // associate permissions to roles
                $role_staff_lead_observatory->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment, $permission_assignment_show
                ]);

                $role_staff_lead_membre->givePermissionTo([
                    $permission_list, $permission_show, $permission_rejected, $permission_self_assignment, $permission_assignment_list, $permission_assignment, $permission_assignment_show
                ]);

            }

            {
                $this->assignRolesStaff('e52e6a29-cfb3-4cdb-9911-ddaed1f17145', $role_staff_lead_observatory, $role_staff_observatory);
                $this->assignRolesStaff('74e98a2d-35ac-472e-911d-190f5a1d3fd6', $role_staff_lead_membre, $role_staff_membre);
            }


        }

    }
}
