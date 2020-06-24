<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffForTreatmentClaimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nature = env('APP_NATURE');

        switch ($nature) {
            case 'MACRO':
                $institutions = Institution::where('id', '7fb9dcbf-0747-4106-a036-0d7f504fc6f9')->orWhere('id', 'b99a6d22-4af1-4a8a-9589-81468f5c020b')->get();
                break;
            case 'PRO':
                $institutions = Institution::where('id', '43ebf6c0-be03-4881-8196-59d476f75c9e')->get();
                break;
            case 'HUB':
                $institutions = Institution::where('id', 'c0cf700f-b7bd-41fe-8640-71d689734ce1')->orWhere('id', 'e52e6a29-cfb3-4cdb-9911-ddaed1f17145')->get();
                break;
        }

        if($nature === 'MACRO' || $nature === 'PRO'){

            foreach ($institutions as $key => $institution){

                    $position_staff = Position::create(['name' => 'Position Staff '.$key, 'description' => 'Position Staff']);

                    $position_staff_lead = Position::create(['name' => 'Position Staff lead '.$key, 'description' => 'Position Staff']);

                    $institution->positions()->attach([$position_staff->id, $position_staff_lead->id]);

                    $units = Unit::where('institution_id', $institution->id)->get();

                    foreach($units as $key_unit => $unit){

                        $key_combine = $key.'-'.$key_unit;

                        // enregistrement d'un staff
                        $identite_staff = Identite::create([
                            'firstname' => 'Staff',
                            'lastname' => 'Staff',
                            'sexe' => 'M',
                            'telephone' => json_encode(['22025842'.$key_combine, '84626846'.$key_combine]),
                            'email' => json_encode(['staff@staff'.$key_combine.'.com', 'staff1@staff'.$key_combine.'.com']),
                            'ville' => 'Cotonou'
                        ]);

                        $staff = Staff::create([
                            'identite_id' => $identite_staff->id,
                            'position_id' => $position_staff->id,
                            'unit_id'    => $unit->id,
                            'institution_id' => $institution->id
                        ]);

                        $user = User::create([
                            'username' => 'staff@staff'.$key_combine.'.com',
                            'password' => bcrypt('123456789'),
                            'identite_id' => $identite_staff->id
                        ]);

                        // Enregistrement d'un staff lead

                        $identite_staff_lead = Identite::create([
                            'firstname' => 'Staff Lead',
                            'lastname' => 'Staff Lead',
                            'sexe' => 'M',
                            'telephone' => json_encode(['022025842'.$key_combine, '084626846'.$key_combine]),
                            'email' => json_encode(['stafflead@staff'.$key_combine.'.com', 'stafflead1@staff'.$key_combine.'.com']),
                            'ville' => 'Cotonou'
                        ]);

                        $staff_lead = Staff::create([
                            'identite_id' => $identite_staff_lead->id,
                            'position_id' => $position_staff_lead->id,
                            'unit_id'       => $unit->id,
                            'institution_id' => $institution->id
                        ]);

                        $user_lead = User::create([
                            'username' => 'stafflead@staff'.$key_combine.'.com',
                            'password' => bcrypt('123456789'),
                            'identite_id' => $identite_staff_lead->id
                        ]);

                        $unit->update([
                            'lead_id' => $staff_lead->id
                        ]);

                    }
                }
        }


        if($nature === 'HUB'){

        }
    }
}
