<?php

namespace Satis2020\MessageApi\Database\Seeds;

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
        Permission::create(['name' => 'list-message-apis', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-message-apis', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-message-apis', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-message-apis', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-institution-message-api', 'guard_name' => 'api']);
        Permission::create(['name' => 'update-my-institution-message-api', 'guard_name' => 'api']);

        $permissions = [
            'list-message-apis',
            'store-message-apis',
            'update-message-apis',
            'destroy-message-apis',
            'update-institution-message-api',
            'update-my-institution-message-api'
        ];

        if ($nature === 'DEVELOP') {
            // get role admin
            Role::where('name', 'admin')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);
        }

        if ($nature === 'MACRO') {

            Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'list-message-apis',
                'store-message-apis',
                'update-message-apis',
                'destroy-message-apis',
                'update-institution-message-api'
            ]);

            Role::where('name', 'admin-filial')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'update-my-institution-message-api'
            ]);

        }

        if ($nature === 'HUB') {

            Role::where('name', 'admin-observatory')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'list-message-apis',
                'store-message-apis',
                'update-message-apis',
                'destroy-message-apis',
                'update-my-institution-message-api'
            ]);

        }

        if ($nature === 'PRO') {

            Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail()->givePermissionTo([
                'list-message-apis',
                'store-message-apis',
                'update-message-apis',
                'destroy-message-apis',
                'update-my-institution-message-api'
            ]);

        }

    }
}
