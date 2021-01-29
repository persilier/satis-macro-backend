<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class UpdateTimeLimitToClaimTableSeeder
 * @package Satis2020\ServicePackage\Database\Seeds
 */
class UpdateTimeLimitToClaimTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $claims = Claim::where('time_limit', NULL)->get();

       if(!empty($claims)){

           foreach ($claims as $claim){

               if($claim->claimObject->time_limit){

                   $claim->update([
                       'time_limit' => $claim->claimObject->time_limit
                   ]);
               }
           }

       }


    }
}
