<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Unit;
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
            ->whereHas('institution.institutionType', function ($query) {
                $query->where('name', '<>', 'holding');
            })
            ->whereHas('unitType', function ($query) {
                $query->where('can_treat', true);
            })->get();

        foreach ($units as $unit) {
            $this->command->info("Assignation des réclamations des réclamation pour l'institution {$unit->name} cours....");

            Claim::query()->where('institution_targeted_id', $unit->institution_id)->inRandomOrder()->take(8)->chunk(2, function ($claims) use ($faker, $unit) {
                foreach ($claims as $claim) {

                    $request = new Request();

                    $request->merge([
                        'unit_id' => $unit->id
                    ]);
                    $this->transferToUnit($request, $claim, false);
                    $claim = $claim->refresh()->load('activeTreatment');

                    $staff = $this->getTargetedStaffFromUnit($unit->id);

                    $claim->activeTreatment->update([
                        'responsible_staff_id' => $staff->random()->id,
                        'assigned_to_staff_by' => $unit->lead_id,
                        'assigned_to_staff_at' => Carbon::parse($claim->created_at)->addDays(random_int(1, 3))->format('Y-m-d H:i:s')
                    ]);

                    $claim->update(['status' => 'assigned_to_staff']);
                }
            });
        }
    }


}
