<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Requirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Unit;

class ProcessingCircuitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('claim_object_unit')->truncate();

        $faker = Faker::create();

        $claimObjects = ClaimObject::all()->pluck('id');

        $units = Unit::all();

        foreach ($units as $unit){
            $unit->claimObjects()->attach($claimObjects->random($faker->numberBetween(1, 4))->all());
        }

    }
}
