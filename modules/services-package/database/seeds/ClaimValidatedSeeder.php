<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\HandleTreatment;

class ClaimValidatedSeeder extends Seeder
{

    use HandleTreatment;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $faker = Faker::create();

        Claim::query()->inRandomOrder()->take(500)->chunk(100, function ($claims) use ($faker) {
            foreach ($claims as $claim) {
                $request = new Request();

                $unit = Unit::query()
                    ->whereHas('unitType', function ($query) {
                        $query->where('can_treat', true);
                    })->inRandomOrder()->first();

                $request->merge([
                    'unit_id' => $unit->id
                ]);
                $this->transferToUnit($request, $claim, false);
                //register a treatment
                $claim = $claim->refresh();

                $claim->load('activeTreatment');
                $claim->activeTreatment->update([
                    'amount_returned' => $claim->amount_disputed,
                    'solution' => $faker->text,
                    'preventive_measures' => $faker->text,
                    'solved_at' => (string)Carbon::parse($faker->dateTimeBetween($claim->created_at,  'now', $timezone = null)),
                    'validated_at' => (string)Carbon::parse($faker->dateTimeBetween($claim->created_at,  'now', $timezone = null)),
                ]);

                //update claim
                $claim->update(['status' => 'treated']);
            }

        });




        // $units = Unit::query()
        //     ->whereHas('institution.institutionType', function ($query) {
        //         $query->where('name', '<>', 'holding');
        //     })
        //     ->whereHas('unitType', function ($query) {
        //         $query->where('can_treat', true);
        //     })->get();

        // foreach ($units as $unit) {
        //     $this->command->info(" Validation des reclamation en cours....");

        //     Claim::query()->where('status', 'assigned_to_staff')
        //         ->where('institution_targeted_id', $unit->institution_id)
        //         ->inRandomOrder()->take(5)->chunk(5, function ($claims) use ($faker, $unit) {
        //             foreach ($claims as $claim) {

        //                 //register a treatment
        //                 $claim = $claim->refresh();

        //                 $claim->load('activeTreatment');
        //                 $claim->activeTreatment->update([
        //                     'amount_returned' => $claim->amount_disputed,
        //                     'solution' => $faker->text,
        //                     'preventive_measures' => $faker->text,
        //                     'solved_at' => Carbon::parse($claim->activeTreatment->assigned_to_staff_at)->addDays(random_int(3, 6))->format('Y-m-d H:i:s'),
        //                     'validated_at' => Carbon::parse($claim->activeTreatment->assigned_to_staff_at)->addDays(random_int(6, 9))->format('Y-m-d H:i:s'),
        //                 ]);

        //                 //update claim
        //                 $claim->update(['status' => 'treated']);
        //             }
        //         });

        //     $this->command->info(" Validation des reclamation terminée....");
        // }
    }
}
