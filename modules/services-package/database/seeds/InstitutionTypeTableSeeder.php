<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitutionTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        InstitutionType::flushEventListeners();

        InstitutionType::create([
            'id' => (string)Str::uuid(),
            'name' => 'holding',
            'description' => "Holding d'un groupe d'institutions",
            "application_type" => "macro",
            "maximum_number_of_institutions" => 1
        ]);

        InstitutionType::create([
            'id' => (string)Str::uuid(),
            'name' => 'filiale',
            'description' => "Filiale d'une holding",
            "application_type" => "macro",
            "maximum_number_of_institutions" => 0
        ]);

        InstitutionType::create([
            'id' => (string)Str::uuid(),
            'name' => 'observatoire',
            'description' => "Observatoire de qualité ou autorité de régulation",
            "application_type" => "hub",
            "maximum_number_of_institutions" => 1
        ]);

        InstitutionType::create([
            'id' => (string)Str::uuid(),
            'name' => 'membre',
            'description' => "Institution affiliée à un observatoire de qualité ou à une autorité de régulation",
            "application_type" => "hub",
            "maximum_number_of_institutions" => 0
        ]);

        InstitutionType::create([
            'id' => (string)Str::uuid(),
            'name' => 'independant',
            'description' => "Institution independante",
            "application_type" => "pro",
            "maximum_number_of_institutions" => 1
        ]);

    }
}
