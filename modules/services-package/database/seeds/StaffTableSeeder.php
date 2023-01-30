<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (Staff::with('unit.institution')->get() as $staff) {
            if (!is_null($staff->unit)) {
                if (!is_null($staff->unit->institution)) {
                    $staff->update(['institution_id' => $staff->unit->institution->id]);
                }
            }
        }
    }
}
