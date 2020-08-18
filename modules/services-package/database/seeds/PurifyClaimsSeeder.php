<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Models\Treatment;

class PurifyClaimsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Treatment::truncate();
        Claim::truncate();
        File::truncate();
        DB::table('discussion_staff')->truncate();
        Discussion::truncate();
        Message::truncate();
    }
}
