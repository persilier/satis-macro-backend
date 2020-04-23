<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Support\Arr;
use Satis2020\ServicePackage\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitLeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Unit::flushEventListeners();
        $units = Unit::has('staffs')->get();
        foreach ($units as $unit){
            $unit->lead()->associate(Arr::random($unit->staffs->all()));
            $unit->save();
        }
    }
}
