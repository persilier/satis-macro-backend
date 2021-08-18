<?php

namespace Satis2020\ReportingClaimMyInstitution\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Class RolesTableSeeder
 * @package Satis2020\ReportingClaimMyInstitution\Database\Seeds
 */
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
        $permission_list = Permission::create(['name' => 'config-reporting-claim-my-institution', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // create admin roles
            $role_pilot = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            $role_pilot->givePermissionTo([
                $permission_list
            ]);
        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin_filial = Role::where('name', 'admin-filial')->where('guard_name', 'api')->firstOrFail();

            $role_pilot_filial= Role::where('name', 'pilot-filial')->where('guard_name', 'api')->firstOrFail();
            // associate permissions to roles
            $role_admin_filial->givePermissionTo([
                $permission_list
            ]);

            $role_pilot_filial->givePermissionTo([
                $permission_list
            ]);
        }

        if ($nature === 'PRO') {

            $role_admin = Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail();

            $role_pilot = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            $role_admin->givePermissionTo([
                $permission_list
            ]);

            $role_pilot->givePermissionTo([
                $permission_list
            ]);
        }
    }

}
