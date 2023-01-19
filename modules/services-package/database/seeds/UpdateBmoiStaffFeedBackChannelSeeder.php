<?php

namespace Satis2020\ServicePackage\Database\Seeds;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Models\Staff;


class UpdateBmoiStaffFeedBackChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $staffs = Staff::all();

        foreach ($staffs as $staff){
            if(($staff->feedback_preferred_channels && !in_array('email',$staff->feedback_preferred_channels))){
                $data = array_merge($staff->feedback_preferred_channels,['email']);
            } else {
                $data = ['email'];
            }
Log::info($data);

            // $staff->update([
            //     'feedback_preferred_channels' => $data
            // ]);
        }

    }
}
