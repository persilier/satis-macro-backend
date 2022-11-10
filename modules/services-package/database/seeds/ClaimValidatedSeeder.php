<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Database\Seeder;
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

        $faker = Faker::create();

        $units = Unit::query()
            ->whereHas('institution.institutionType',function ($query){
                $query->where('name','<>','holding');
            })
            ->whereHas('unitType',function ($query){
                $query->where('can_treat',true);
            })->get();

        foreach ($units as $unit){
            $this->command->info(" Validation des reclamation en cours....");

            Claim::query()->where('status','assigned_to_staff')
                ->where('institution_targeted_id',$unit->institution_id)
                ->inRandomOrder()->take(5)->chunk(5,function ($claims) use($faker,$unit){
                foreach ($claims as $claim) {

                    //register a treatment
                    $claim = $claim->refresh();

                    $claim->load('activeTreatment');
                    $claim->activeTreatment->update([
                        'amount_returned' => $claim->amount_disputed,
                        'solution' => $faker->text,
                        'preventive_measures' => $faker->text,
                        'solved_at' => Carbon::parse($claim->activeTreatment->assigned_to_staff_at)->addDays(random_int(1,3)),
                        'validated_at' => Carbon::parse($claim->activeTreatment->assigned_to_staff_at)->addDays(random_int(3,6)),
                    ]);

                    //update claim
                    $claim->update(['status' => 'treated']);
                }
            });

            $this->command->info(" Validation des reclamation terminée....");
        }
    }


}
