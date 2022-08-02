<?php

use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Models\Claim;

class AddStaffToTreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Claim::query()
            ->with('activeTreatment')
            ->whereHas('treatments')
            ->chunk(50,function ($claims){
                foreach ($claims as $claim){
                    $activeTreatment = $claim->activeTreatment;
                    $activeTreatment->update([
                        'transferred_to_targeted_institution_by'=>$claim->institution_targeted_id,
                        'transferred_to_unit_by'=>$claim->institution_targeted_id,
                        'validated_by'=>$claim->institution_targeted_id
                    ]);
                }
        });
    }
}
