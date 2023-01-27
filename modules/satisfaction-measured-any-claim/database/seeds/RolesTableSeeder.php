<?php

namespace Satis2020\SatisfactionMeasuredAnyClaim\Database\Seeds;

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
 * @package Satis2020\SatisfactionMeasuredAnyClaim\Database\Seeds
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
        $permission_list = Permission::create(['name' => 'list-satisfaction-measured-any-claim', 'guard_name' => 'api']);
        $permission_update = Permission::create(['name' => 'update-satisfaction-measured-any-claim', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // create admin roles
            $role_admin = Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail();

            $role_admin->givePermissionTo([
                $permission_list, $permission_update
            ]);
        }

        if ($nature === 'HUB') {

            $role_collector = Role::where('name', 'collector-observatory')->where('guard_name', 'api')->firstOrFail();

            $role_pilot = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            $role_collector->givePermissionTo([
                $permission_list, $permission_update
            ]);

            $role_pilot->givePermissionTo([
                $permission_list, $permission_update
            ]);
        }
    }

}
