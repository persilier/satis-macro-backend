<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Institution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitutionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Institution::truncate();
        Institution::flushEventListeners();
        factory(\Satis2020\ServicePackage\Models\Institution::class, 5)->create();
    }
}
