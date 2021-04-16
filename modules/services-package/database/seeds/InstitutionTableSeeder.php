<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Institution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Satis2020\ServicePackage\Models\InstitutionType;

class InstitutionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Institution::truncate();
        Institution::flushEventListeners();

        $app_nature = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'app-nature')->first()->data);
        switch ($app_nature) {

            case 'pro':
                $name = $faker->word;
                $institutionType = InstitutionType::where('name', 'independant')->first();
                Institution::create([
                    'id' => (string)Str::uuid(),
                    'slug' => Str::slug($name),
                    'name' => $name,
                    'acronyme' => $faker->randomLetter,
                    'iso_code' => $faker->iso8601,
                    'institution_type_id' => $institutionType->id
                ]);
                break;

            case 'hub':
                $name = $faker->word;
                $institutionType = InstitutionType::where('name', 'observatoire')->first();
                Institution::create([
                    'id' => (string)Str::uuid(),
                    'slug' => Str::slug($name),
                    'name' => $name,
                    'acronyme' => $faker->randomLetter,
                    'iso_code' => $faker->iso8601,
                    'institution_type_id' => $institutionType->id
                ]);
                factory(\Satis2020\ServicePackage\Models\Institution::class, 5)->create();
                break;

            case 'macro':
                $name = $faker->word;
                $institutionType = InstitutionType::where('name', 'holding')->first();
                Institution::create([
                    'id' => (string)Str::uuid(),
                    'slug' => Str::slug($name),
                    'name' => $name,
                    'acronyme' => $faker->randomLetter,
                    'iso_code' => $faker->iso8601,
                    'institution_type_id' => $institutionType->id
                ]);
                factory(\Satis2020\ServicePackage\Models\Institution::class, 5)->create();
                break;

        }
    }
}
