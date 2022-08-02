<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\User;

class UpdatePasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         User::query()->update([
            "password"=>bcrypt("password"),
             "disabled_at"=>null
        ]);
    }
}
