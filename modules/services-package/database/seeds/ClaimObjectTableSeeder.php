<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\ClaimObject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClaimObjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        ClaimObject::truncate();
        ClaimObject::flushEventListeners();
        factory(\Satis2020\ServicePackage\Models\ClaimObject::class, 10)->create();
    }
}
