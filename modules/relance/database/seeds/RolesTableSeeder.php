<?php

namespace Satis2020\Relance\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Class RolesTableSeeder
 * @package Satis2020\Relance\Database\Seeds
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

        if($nature === 'PRO'){

            $permission_my_relance = Permission::create(['name' => 'my-send-relance', 'guard_name' => 'api']);

        }elseif($nature === 'HUB'){

            $permission_any_relance = Permission::create(['name' => 'any-send-relance', 'guard_name' => 'api']);

        }else{

            $permission_any_relance = Permission::create(['name' => 'any-send-relance', 'guard_name' => 'api']);
            $permission_my_relance = Permission::create(['name' => 'my-send-relance', 'guard_name' => 'api']);

        }

        if ($nature === 'DEVELOP') {
            // get role admin
            $role_admin = Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin->givePermissionTo([
                $permission_any_relance, $permission_my_relance
            ]);

        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin_holding = Role::where('name', 'pilot-holding')->where('guard_name', 'api')->firstOrFail();

            $role_admin_filial = Role::where('name', 'pilot-filial')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_holding->givePermissionTo([
                $permission_any_relance
            ]);

            $role_admin_filial->givePermissionTo([
                $permission_my_relance
            ]);

        }

        if ($nature === 'HUB') {
            // create admin roles
            $role_admin_hub = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_hub->givePermissionTo([
                $permission_any_relance
            ]);

        }

        if ($nature === 'PRO') {

            $role_admin_pro = Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_pro->givePermissionTo([
                $permission_my_relance
            ]);

        }

    }
}
