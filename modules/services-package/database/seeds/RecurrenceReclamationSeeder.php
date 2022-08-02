<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecurrenceReclamationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        Metadata::create([
            'id' => (string)Str::uuid(),
            'name' => 'recurrence-alert-settings',
            'data' => json_encode([
                'recurrence_period' => 1,
                'max' => 2
            ])
        ]);

    }
}
