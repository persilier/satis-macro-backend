<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class TruncateNotifJobs
 * @package Satis2020\ServicePackage\Database\Seeds
 */
class TruncateNotifJobs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('notifications')->truncate();
        DB::table('jobs')->truncate();
        DB::table('messages')->truncate();
        DB::table('discussion_staff')->truncate();
        DB::table('discussions')->truncate();
        DB::table('treatments')->truncate();
        DB::table('claims')->truncate();
    }
}
