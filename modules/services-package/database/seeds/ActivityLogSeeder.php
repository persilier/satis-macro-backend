<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Activity;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;

class ActivityLogSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
//        Activity::truncate();
//        Activity::flushEventListeners();

        $institutions = Institution::all();

        foreach ($institutions as $institution) {

            $staffs = Staff::where('institution_id', $institution->id)->get();

            $total = 0;

            foreach ($staffs as $index => $staff) {

                for ($total = 0 ; $total <= 10; $total++) {
                    $this->store($staff, $institution);
                }
            }
        }
    }


    protected function store($staff, $institution)
    {
         Activity::create([
            'log_name' => 'faq_category',
            'description' => 'This model faq_category has been deleted ',
            'causer_id' => $staff->identite->user->id,
            'causer_type' => 'Satis2020\ServicePackage\Models\User',
            'ip_address' => request()->ip,
            'institution_id' => $institution->id,
            'log_action' => ['CREATED', 'UPDATED', 'DELETED'][random_int(0, 2)]
        ]);
    }

}