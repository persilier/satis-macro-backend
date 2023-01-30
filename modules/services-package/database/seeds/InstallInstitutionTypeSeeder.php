<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\InstitutionType;

class InstallInstitutionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $institutionTypes = [
            'MACRO' => [
                [
                    'name' => 'holding',
                    'description' => 'Holding d\'un groupe d\'institutions',
                    'application_type' => 'macro',
                    'maximum_number_of_institutions' => 1,
                ],
                [
                    'name' => 'filiale',
                    'description' => 'Filiale d\'une holding',
                    'application_type' => 'macro',
                    'maximum_number_of_institutions' => 0,
                ]
            ],
            'HUB' => [
                [
                    'name' => 'observatory',
                    'description' => 'Observatoire de qualité ou autorité de régulation',
                    'application_type' => 'hub',
                    'maximum_number_of_institutions' => 1,
                ],
                [
                    'name' => 'membre',
                    'description' => 'Institution affiliée à un observatoire de qualité ou à une autorité de régulation',
                    'application_type' => 'hub',
                    'maximum_number_of_institutions' => 0,
                ]
            ],
            'PRO' => [
                [
                    'name' => 'independant',
                    'description' => 'Institution independante',
                    'application_type' => 'pro',
                    'maximum_number_of_institutions' => 1,
                ]
            ]
        ];

        $appNature = Config::get('services.app_nature', 'PRO');

        foreach ($institutionTypes[$appNature] as $institutionType) {
            InstitutionType::create($institutionType);
        }

    }
}
