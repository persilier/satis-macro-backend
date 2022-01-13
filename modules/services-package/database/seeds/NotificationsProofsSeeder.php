<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\NotificationProof;
use Faker\Generator as Faker;

class NotificationsProofsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        NotificationProof::query()->truncate();
        NotificationProof::flushEventListeners();

        factory(NotificationProof::class, 50)->create();

    }
}
