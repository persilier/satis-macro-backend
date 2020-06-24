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
        $n = 0;

        if($nature === 'MACRO'){

            {
                //Institution Holding
                $institution_holding = Institution::find('3d7f426e-494a-4650-a615-315db1b38c52');

                $unit = Unit::whereHas('staffs')->where('institution_id', $institution_holding->id)->first();


                $staffs = Staff::with(['identite'])->whereHas('identite', function ($query) {
                    $query->doesntHave('user');
                })->where('institution_id', $institution_holding->id)->get();


                foreach ($staffs as $key => $staff){

                    if($staff->unit_id == $unit->id){
                        if($n < 2){
                            $user = User::create([
                                'username' => $staff->identite->email[0],
                                'password' => bcrypt('123456789'),
                                'identite_id' => $staff->identite->id
                            ]);
                        }

                        if($n==0){
                            $unit->update(['lead_id', $staff->id]);
                        }

                        $n++;
                    }

                }
                $n = 0;

            }

            // Institution Filial
            {
                $institution_filial = Institution::find('b99a6d22-4af1-4a8a-9589-81468f5c020b');

                $unit = Unit::whereHas('staffs')->where('institution_id', $institution_filial->id)->firstOrFail();

                $staffs = Staff::with(['identite'])->whereHas('identite', function ($query) {
                    $query->doesntHave('user');
                })->where('institution_id', $institution_filial->id)->get();


                foreach ($staffs as $key => $staff){

                    if($staff->unit_id == $unit->id){
                        if($n < 2){
                            $user = User::create([
                                'username' => $staff->identite->email[0],
                                'password' => bcrypt('123456789'),
                                'identite_id' => $staff->identite->id
                            ]);
                        }

                        if($n==0){
                            $unit->update(['lead_id', $staff->id]);
                        }

                        $n++;
                    }

                }
                $n = 0;
            }

       }

        if($nature === 'PRO'){

            // Institution pro
            {
                $institution_pro= Institution::find('43ebf6c0-be03-4881-8196-59d476f75c9e');

                $unit = Unit::whereHas('staffs')->where('institution_id', $institution_pro->id)->firstOrFail();

                $staffs = Staff::with(['identite'])->whereHas('identite', function ($query) {
                    $query->doesntHave('user');
                })->where('institution_id', $institution_pro->id)->get();


                foreach ($staffs as $key => $staff){

                    if($staff->unit_id == $unit->id){
                        if($n < 2){
                            $user = User::create([
                                'username' => $staff->identite->email[0],
                                'password' => bcrypt('123456789'),
                                'identite_id' => $staff->identite->id
                            ]);
                        }

                        if($n==0){
                            $unit->update(['lead_id', $staff->id]);
                        }

                        $n++;
                    }

                }
                $n = 0;
            }
        }


        if($nature === 'HUB'){

            // Institution observatoire
            {
                $institution_observatory= Institution::find('e52e6a29-cfb3-4cdb-9911-ddaed1f17145');

                $unit = Unit::whereHas('staffs')->first();

                $staffs = Staff::with(['identite'])->whereHas('identite', function ($query) {
                    $query->doesntHave('user');
                })->where('institution_id', $institution_observatory->id)->get();


                foreach ($staffs as $staff){

                    if($staff->unit_id == $unit->id){
                        if($n < 2){
                            $user = User::create([
                                'username' => $staff->identite->email[0],
                                'password' => bcrypt('123456789'),
                                'identite_id' => $staff->identite->id
                            ]);
                        }

                        if($n==0){
                            $unit->update(['lead_id', $staff->id]);
                        }

                        $n++;
                    }

                }
                $n = 0;
            }



            // Institution membre
            {
                $institution_membre= Institution::find('74e98a2d-35ac-472e-911d-190f5a1d3fd6');

                $unit =  Unit::whereHas('staffs');

                $staffs = Staff::with(['identite'])->whereHas('identite', function ($query) {
                    $query->doesntHave('user');
                })->where('institution_id', $institution_membre->id)->get();


                foreach ($staffs as $staff){

                    if($staff->unit_id == $unit->id){
                        if($n < 2){
                            $user = User::create([
                                'username' => $staff->identite->email[0],
                                'password' => bcrypt('123456789'),
                                'identite_id' => $staff->identite->id
                            ]);
                        }

                        if($n==0){
                            $unit->update(['lead_id', $staff->id]);
                        }

                        $n++;
                    }

                }
                $n = 0;
            }


        }


    }
}
