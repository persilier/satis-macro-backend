<?php

namespace Satis2020\Configuration\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ComponentSeeder extends Seeder
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
        if (is_null(Permission::where('name', 'update-components-parameters')->where('guard_name', 'api')->first())) {
            Permission::create(['name' => 'update-components-parameters', 'guard_name' => 'api']);
        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin_holding = Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_holding->givePermissionTo([
                'update-components-parameters'
            ]);

        }

        if ($nature == 'HUB') {
            // retrieve admin roles
            $role_admin_observatory = Role::where('name', 'admin-observatory')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_observatory->givePermissionTo([
                'update-components-parameters'
            ]);
        }

        if ($nature == 'PRO') {
            $role_admin_pro = Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_pro->givePermissionTo([
                'update-components-parameters'
            ]);

        }

    }
}
