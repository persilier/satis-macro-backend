<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Models\UnitType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class StaffForUnitTreatmentClaimSeeder extends Seeder
{

    function storeStaffUnitTreatment($institutionId, $type, $phone){

        $institution = Institution::find($institutionId);

        $type_unit = UnitType::create([
            'name' => Str::random(10),
            'description' => Str::random(100),
            'can_be_target' => 1,
            'can_treat' => 1
        ]);

        $unit = Unit::create([
            'name' => Str::random(10),
            'description' => Str::random(100),
            'unit_type_id' =>  $type_unit->id,
            'institution_id'=> $institution->id
        ]);

        for($n=0; $n < 2; $n++){

            $identite = Identite::create([
                'firstname' => Str::random(10),
                'lastname' => Str::random(10),
                'sexe' => 'M',
                'telephone' =>  [$phone.$n],
                'email' => [Str::random(5).$type.'@staff.com'],
                'ville' => Str::random(10),
            ]);

            $position = Position::create([
                'name'=> Str::random(10),
                'description' => Str::random(100)
            ]);

            $institution->positions()->sync([$position->id]);

            $staff = Staff::create([
                'identite_id'=> $identite->id,
                'unit_id' => $unit->id,
                'institution_id' => $institution->id,
                'position_id' => $position->id
            ]);

            $user = User::create([
                'username' => $identite->email[0],
                'password' => bcrypt('123456789'),
                'identite_id' => $identite->id
            ]);

            if($n==0){
                $unit->update(['lead_id' => $staff->id]);
            }
        }
        return true;
    }


    function storeStaffUnitTreatmentHub($institutionId, $type, $phone){

        $institution = Institution::find($institutionId);

        $type_unit = UnitType::create([
            'name' => Str::random(10),
            'description' => Str::random(100),
            'can_be_target' => 1,
            'can_treat' => 1
        ]);

        $unit = Unit::create([
            'name' => Str::random(10),
            'description' => Str::random(100),
            'unit_type_id'=>  $type_unit->id
        ]);

        for($n=0; $n < 7; $n++){

            $identite = Identite::create([
                'firstname' => Str::random(10),
                'lastname' => Str::random(10),
                'sexe' => 'M',
                'telephone' =>  [$phone.$n],
                'email' => [Str::random(5).$type.'@staff.com'],
                'ville' => Str::random(10),
            ]);

            $position = Position::create([
                'name'=> Str::random(10),
                'description' => Str::random(100)
            ]);

            $institution->positions()->sync([$position->id]);

            $staff = Staff::create([
                'identite_id'=> $identite->id,
                'unit_id' => $unit->id,
                'institution_id' => $institution->id,
                'position_id' => $position->id
            ]);

            $user = User::create([
                'username' => $identite->email[0],
                'password' => bcrypt('123456789'),
                'identite_id' => $identite->id
            ]);

            if($n==0){
                $unit->update(['lead_id' => $staff->id]);
            }
        }
        return true;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nature = env('APP_NATURE');


        if($nature === 'MACRO'){

            // holding
            $this->storeStaffUnitTreatment('3d7f426e-494a-4650-a615-315db1b38c52', 'holding', '059859568');

            // filial
            $this->storeStaffUnitTreatment('b99a6d22-4af1-4a8a-9589-81468f5c020b', 'filial', '05989568');
        }

        if($nature === 'PRO'){
            // pro
            $this->storeStaffUnitTreatment('43ebf6c0-be03-4881-8196-59d476f75c9e', 'pro', '059856802');
        }

        if($nature === 'HUB'){
            // observatory
            $this->storeStaffUnitTreatmentHub('e52e6a29-cfb3-4cdb-9911-ddaed1f17145', 'observatory', '00598568');

            // membre
            $this->storeStaffUnitTreatmentHub('74e98a2d-35ac-472e-911d-190f5a1d3fd6', 'membre', '0549859568');
        }
    }
}
