<?php

namespace Satis2020\Escalation\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EscalationConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $escalationSettings = [
            'standard_bord_exists' => true,
            'specific_bord_exists' => false,
           ];

        Metadata::query()->updateOrCreate([
            "name"=>Metadata::ESCALATION
        ],[
            'id' => (string)Str::uuid(),
            'name' => Metadata::ESCALATION,
            'data' => json_encode($escalationSettings)
        ]);

    }
}
