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

class SmsTableSeeder extends Seeder
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

        if (\Satis2020\ServicePackage\Models\Metadata::where('name', 'sms-parameters')->get()->count() == 0) {
            $sms_parameters = [
                'senderID' => 'default',
                'username' => "default",
                'password' => 'default',
                'indicatif' => '00229',
                'api' => 'default'
            ];

            Metadata::create([
                'id' => (string)Str::uuid(),
                'name' => 'sms-parameters',
                'data' => json_encode($sms_parameters)
            ]);
        }


        // create staff from any unit permissions
        $permission_show = Permission::create(['name' => 'show-sms-parameters', 'guard_name' => 'api']);
        $permission_update = Permission::create(['name' => 'update-sms-parameters', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // get role admin
            $role_admin = Role::where('name', 'admin')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin->givePermissionTo([
                $permission_update, $permission_show
            ]);

        }

        if ($nature === 'MACRO') {
            // create admin roles
            $role_admin_holding = Role::where('name', 'admin-holding')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_holding->givePermissionTo([
                $permission_update, $permission_show
            ]);

        }

        if ($nature == 'HUB') {
            // retrieve admin roles
            $role_admin_observatory = Role::where('name', 'admin-observatory')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_observatory->givePermissionTo([
                $permission_update, $permission_show
            ]);
        }

        if ($nature == 'PRO') {
            $role_admin_pro = Role::where('name', 'admin-pro')->where('guard_name', 'api')->firstOrFail();

            // associate permissions to roles
            $role_admin_pro->givePermissionTo([
                $permission_update, $permission_show
            ]);

        }

    }
}
