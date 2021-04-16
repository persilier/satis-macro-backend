<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


/**
 * Class TauxRelanceSendNotificationSeeder
 * @package Satis2020\ServicePackage\Database\Seeds
 */
class TauxRelanceSendNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Metadata::create([
            'id' => (string)Str::uuid(),
            'name' => 'coef-relance',
            'data' => json_encode(50)
        ]);

    }
}
