<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ClaimAwaitingValidationSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $faker = Faker::create();

        $claims = Claim::with('activeTreatment')->get();

        foreach ($claims as $claim) {

            //register a treatment
            $claim->activeTreatment->update([
                'amount_returned' => $claim->amount_disputed,
                'solution' => $faker->text,
                'preventive_measures' => $faker->text,
                'solved_at' => Carbon::now()
            ]);

            //update claim
            $claim->update(['status' => 'treated']);
        }

    }
}
