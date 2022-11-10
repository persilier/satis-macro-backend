<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Notifications\TransferredToUnit;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Traits\HandleTreatment;

class AssignClaimToStaffSeeder extends Seeder
{

    use ClaimAwaitingTreatment, HandleTreatment;

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
            $this->command->info("Assignation des réclamations des réclamation pour l'institution {$unit->name} cours....");

            Claim::query()->where('institution_targeted_id',$unit->institution_id)->inRandomOrder()->take(40)->chunk(5,function ($claims) use($faker,$unit){
                foreach ($claims as $claim) {

                    $request = new Request();

                    $request->merge([
                        'unit_id' =>$unit->id
                    ]);
                    $this->transferToUnit($request, $claim, false);
                    $claim = $claim->refresh()->load('activeTreatment');

                    $staff = $this->getTargetedStaffFromUnit($unit->id);
                    $claim->activeTreatment->update([
                        'responsible_staff_id' => $staff->random()->first()->id,
                        'assigned_to_staff_by' => $unit->lead_id,
                        'assigned_to_staff_at' => Carbon::parse($faker->dateTimeBetween($claim->created_at, 'now', $timezone = null))]);

                    $claim->update(['status' => 'assigned_to_staff']);
                }
            });
        }
    }


}
