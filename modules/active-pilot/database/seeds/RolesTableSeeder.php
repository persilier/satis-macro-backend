<?php

namespace Satis2020\ActivePilot\Database\Seeds;

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
        $permission_update = Permission::where('name', 'update-active-pilot')->where('guard_name', 'api')->first();
        
        if(is_null($permission_update)){
            $permission_update = Permission::create(['name' => 'update-active-pilot', 'guard_name' => 'api']);
        }
        

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin_holding = Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_holding->givePermissionTo([
                $permission_update,
            ]);

            $role_pilot_holding = Role::where('name', 'pilot-holding')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_pilot_holding->givePermissionTo([
                $permission_update,
            ]);

            // create admin roles
            $role_admin_filial = Role::where('name', 'admin-filial')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_filial->givePermissionTo([
                $permission_update,
            ]);

            $role_pilot_filial = Role::where('name', 'pilot-filial')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_pilot_filial->givePermissionTo([
                $permission_update,
            ]);

        }

        if ($nature === 'HUB') {
            // create admin roles
            $role_admin_hub = Role::where('name', 'admin-observatory')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_hub->givePermissionTo([
                $permission_update,
            ]);

            // create admin roles
            $role_pilot = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_pilot->givePermissionTo([
                $permission_update,
            ]);

        }

        if ($nature === 'PRO') {
            $role_admin_pro = Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_pro->givePermissionTo([
                $permission_update,
            ]);

            // create admin roles
            $role_pilot = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_pilot->givePermissionTo([
                $permission_update,
            ]);

        }

    }
}
