<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionType;

class InstallInstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $institutions = [
            'MACRO' => [
                'slug' => Str::slug('Holding', '-'),
                'name' => 'Holding',
                'acronyme' => Str::slug('Holding', '-'),
                'iso_code' => '229',
                'institution_type_id' => InstitutionType::firstOrFail('name', 'holding')->id,
            ],
            'HUB' => [
                'slug' => Str::slug('Observatory', '-'),
                'name' => 'Observatory',
                'acronyme' => Str::slug('Observatory', '-'),
                'iso_code' => '229',
                'institution_type_id' => InstitutionType::firstOrFail('name', 'observatory')->id,
            ],
            'PRO' => [
                'slug' => Str::slug('Independant', '-'),
                'name' => 'Independant',
                'acronyme' => Str::slug('Independant', '-'),
                'iso_code' => '229',
                'institution_type_id' => InstitutionType::firstOrFail('name', 'independant')->id,
            ]
        ];

        $appNature = Config::get('services.app_nature', 'PRO');

        Institution::create($institutions[$appNature]);

    }
}
