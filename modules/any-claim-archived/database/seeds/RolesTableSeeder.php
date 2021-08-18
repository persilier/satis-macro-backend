<?php

namespace Satis2020\AnyClaimArchived\Database\Seeds;

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
 * Class ArchivedRolesTableSeeder
 * @package Satis2020\ClaimSatisfactionMeasured\Database\Seeds
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

        $permission_archived_list = Permission::create(['name' => 'list-any-claim-archived', 'guard_name' => 'api']);
        $permission_archived_show = Permission::create(['name' => 'show-any-claim-archived', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // create admin roles
            $role_admin = Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail();

            $role_admin->givePermissionTo([
                $permission_archived_list, $permission_archived_show
            ]);
        }

        if ($nature === 'MACRO') {

            $role_pilot_holding = Role::where('name', 'pilot-holding')->where('guard_name', 'api')->firstOrFail();
            // associate permissions to roles
            $role_pilot_holding->givePermissionTo([
                $permission_archived_list, $permission_archived_show
            ]);

        }

        if ($nature === 'HUB') {

            $role_pilot = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            $role_pilot->givePermissionTo([
                $permission_archived_list, $permission_archived_show
            ]);
        }
    }

}
