<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\CategoryClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Client;

class ClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Client::truncate();
        Client::flushEventListeners();
        factory(\Satis2020\ServicePackage\Models\Client::class, 15)->create();
    }
}
