<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Institution;

class PurifyInstitutionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (Institution::all() as $institution) {
            $institution->secureForceDeleteWithoutException('units', 'positions', 'institutionType', 'staff', 'client_institutions', 'claims',
                'institutionMessageApi');
        }
    }
}
