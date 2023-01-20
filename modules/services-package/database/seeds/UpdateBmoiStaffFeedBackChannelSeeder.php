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

        foreach ($staffs as $staff) {
            $data = [];
            if (!$staff->feedback_preferred_channels) {
                $data = array_merge([], ['email']);
            } else if (!in_array('email', $staff->feedback_preferred_channels)) {
                $data = array_merge($staff->feedback_preferred_channels, ['email']);
            } else {
                $data = $staff->feedback_preferred_channels;  
            }

            $staff->update([
                'feedback_preferred_channels' => $data
            ]);
        }
    }
}
