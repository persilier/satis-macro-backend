<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegulatoryLimitMetadataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Metadata::query()->updateOrCreate(["name" => Metadata::REGULATORY_LIMIT],[
            'id' => (string)Str::uuid(),
            'name' => 'regulatory-limit',
            'data' => 45
        ]);
    }
}
