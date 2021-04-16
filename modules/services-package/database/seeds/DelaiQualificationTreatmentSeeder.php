<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DelaiQualificationTreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $qualification_parameters = [

            [
                'uuid' => (string)Str::uuid(), 'borne_inf' => 10, 'borne_sup' => '+'
            ],
            [
                'uuid' => (string)Str::uuid(),'borne_inf' => 2, 'borne_sup' => 4
            ],
            [
                'uuid' => (string)Str::uuid(),'borne_inf' => 4, 'borne_sup' => 6
            ],
            [
                'uuid' => (string)Str::uuid(),'borne_inf' => 6, 'borne_sup' => 10
            ],
            [
                'uuid' => (string)Str::uuid(),'borne_inf' => 0, 'borne_sup' => 2
            ],
        ];

        $treatments_parameters = [
            [
                'uuid' => (string)Str::uuid(),'borne_inf' => 0, 'borne_sup' => 2
            ],
            [
                'uuid' => (string)Str::uuid(),'borne_inf' => 2, 'borne_sup' => 4
            ],
            [
                'uuid' => (string)Str::uuid(),'borne_inf' => 4, 'borne_sup' => 6
            ],
            [
                'uuid' => (string)Str::uuid(),'borne_inf' => 6, 'borne_sup' => 10
            ],
            [
                'uuid' => (string)Str::uuid(),'borne_inf' => 10, 'borne_sup' => '+'
            ],
        ];

        Metadata::create([
            'id' => (string)Str::uuid(),
            'name' => 'delai-qualification-parameters',
            'data' => json_encode($qualification_parameters)
        ]);

        Metadata::create([
            'id' => (string)Str::uuid(),
            'name' => 'delai-treatment-parameters',
            'data' => json_encode($treatments_parameters)
        ]);

    }
}
