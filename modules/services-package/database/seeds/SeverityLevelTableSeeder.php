<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\ClaimCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\SeverityLevel;

class SeverityLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        SeverityLevel::truncate();
        SeverityLevel::flushEventListeners();
        factory(\Satis2020\ServicePackage\Models\SeverityLevel::class, 3)->create();
    }
}
