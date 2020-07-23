<?php

namespace Satis2020\Discussion\Database\Seeds;

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
        Permission::create(['name' => 'list-my-discussions', 'guard_name' => 'api']);
        Permission::create(['name' => 'store-discussion', 'guard_name' => 'api']);
        Permission::create(['name' => 'destroy-discussion', 'guard_name' => 'api']);
        Permission::create(['name' => 'list-discussion-contributors', 'guard_name' => 'api']);
        Permission::create(['name' => 'add-discussion-contributor', 'guard_name' => 'api']);
        Permission::create(['name' => 'remove-discussion-contributor', 'guard_name' => 'api']);
        Permission::create(['name' => 'contribute-discussion', 'guard_name' => 'api']);

        $permissions = [
            'list-my-discussions',
            'store-discussion',
            'destroy-discussion',
            'list-discussion-contributors',
            'add-discussion-contributor',
            'remove-discussion-contributor',
            'contribute-discussion',
        ];

        if ($nature === 'DEVELOP') {
            // get role admin
            Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

            Role::where('name', 'staff')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

        }

        if ($nature === 'MACRO') {

            Role::where('name', 'pilot-holding')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

            Role::where('name', 'pilot-filial')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

            Role::where('name', 'staff')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

        }

        if ($nature === 'HUB') {

            Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

            Role::where('name', 'staff')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

        }

        if ($nature === 'PRO') {

            Role::where('name', 'pilot')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

            Role::where('name', 'staff')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

        }

    }
}
