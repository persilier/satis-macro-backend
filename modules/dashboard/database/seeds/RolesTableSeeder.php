<?php

namespace Satis2020\Dashboard\Database\Seeds;

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
        Permission::create(['name' => 'show-dashboard-data-all-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-dashboard-data-my-institution', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-dashboard-data-my-unit', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-dashboard-data-my-activity', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // get role admin
            $role_admin = Role::where('name', 'admin')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin->givePermissionTo([
                'show-dashboard-data-all-institution',
                'show-dashboard-data-my-institution',
                'show-dashboard-data-my-unit',
                'show-dashboard-data-my-activity'
            ]);
        }

        if ($nature === 'MACRO') {

            Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-all-institution',
                'show-dashboard-data-my-institution'
            ]);

            Role::where('name', 'admin-filial')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-institution'
            ]);

            Role::where('name', 'pilot-holding')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-all-institution',
                'show-dashboard-data-my-institution'
            ]);

            Role::where('name', 'pilot-filial')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-institution'
            ]);

            Role::where('name', 'supervisor-holding')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-all-institution',
                'show-dashboard-data-my-institution'
            ]);

            Role::where('name', 'supervisor-filial')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-institution'
            ]);

            Role::where('name', 'collector-holding')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-activity'
            ]);

            Role::where('name', 'collector-filial-pro')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-activity'
            ]);

            Role::where('name', 'staff')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-unit',
                'show-dashboard-data-my-activity'
            ]);
        }

        if ($nature === 'HUB') {

            Role::where('name', 'admin-observatory')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-all-institution'
            ]);

            Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-all-institution'
            ]);

            Role::where('name', 'supervisor-observatory')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-all-institution'
            ]);

            Role::where('name', 'collector-observatory')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-activity'
            ]);

            Role::where('name', 'staff')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-activity'
            ]);
        }

        if ($nature === 'PRO') {

            Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-institution'
            ]);

            Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-institution'
            ]);

            Role::where('name', 'supervisor-pro')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-institution'
            ]);

            Role::where('name', 'collector-filial-pro')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-activity'
            ]);

            Role::where('name', 'staff')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'show-dashboard-data-my-unit',
                'show-dashboard-data-my-activity'
            ]);
        }
    }
}
