<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Role;

class AssignClaimToStaffSeeder extends Seeder
{

    public function retrieveUnitStaff($unit_id)
    {

        $staff = Staff::with('identite.user')
            ->get()
            ->filter(function ($value, $key) use ($unit_id) {
                return $value->unit_id == $unit_id && $value->identite->user->hasRole('staff');
            });

        return $staff->random();

    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $claims = Claim::with('createdBy')->get();

        foreach ($claims as $claim) {

            $staff = $this->retrieveUnitStaff($claim->createdBy->unit_id);

            //register a treatment
            $activeTreatment = Treatment::create([
                'id' => (string)Str::uuid(),
                'claim_id' => $claim->id,
                'transferred_to_unit_at' => $claim->created_at->addDays($faker->numberBetween(2, 4)),
                'responsible_unit_id' => $claim->createdBy->unit_id,
                'assigned_to_staff_at' => $claim->created_at->addDays($faker->numberBetween(5, 8)),
                'assigned_to_staff_by' => $staff->id,
                'responsible_staff_id' => $staff->id
            ]);

            //update claim
            $claim->update(['active_treatment_id' => $activeTreatment->id, 'status' => 'assigned_to_staff']);

        }

    }
}
