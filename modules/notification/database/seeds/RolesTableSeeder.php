<?php

namespace Satis2020\Notification\Database\Seeds;

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
        Permission::create(['name' => 'update-notifications', 'guard_name' => 'api']);

        $permissions = [
            'update-notifications',
        ];

        if ($nature === 'DEVELOP') {
            // get role admin
            Role::where('name', 'admin')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);
        }

        if ($nature === 'MACRO') {

            Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

        }

        if ($nature === 'HUB') {

            Role::where('name', 'admin-observatory')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

        }

        if ($nature === 'PRO') {

            Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail()->givePermissionTo($permissions);

        }

    }
}
