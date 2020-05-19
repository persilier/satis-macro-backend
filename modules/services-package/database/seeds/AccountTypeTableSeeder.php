<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        AccountType::truncate();
        AccountType::flushEventListeners();
        factory(\Satis2020\ServicePackage\Models\AccountType::class, 5)->create();
    }
}
