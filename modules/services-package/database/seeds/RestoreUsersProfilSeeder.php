<?php

namespace Satis2020\ServicePackage\Database\Seeds;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Role;
use Satis2020\ServicePackage\Models\User;

class RestoreUsersProfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::query()
            ->where('name',"admin-pro")
            ->first();
        $admin = User::query()
            ->where("username","contact@dmdsatis.com")
            ->first();

        DB::table("model_has_roles")
            ->where("model_uuid","<>",$admin->id)
            ->orWhere("role_id","<>",$adminRole->id)
            ->delete();
    }
}
