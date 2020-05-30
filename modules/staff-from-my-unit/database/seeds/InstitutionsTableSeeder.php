<?php

namespace Satis2020\StaffFromMyUnit\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstitutionsTableSeeder extends Seeder
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
        Role::flushEventListeners();

        $nature = env('APP_NATURE');

        if ($nature === 'DEVELOP') {

            // create holding
            $name = 'HOLDING';
            $institutionType = InstitutionType::where('name', 'holding')->first();
            Institution::create([
                'id' => (string)Str::uuid(),
                'slug' => Str::slug($name),
                'name' => $name,
                'acronyme' => $faker->randomLetter,
                'iso_code' => $faker->iso8601,
                'institution_type_id' => $institutionType->id
            ]);

            // create observatory
            $name = 'OBSERVATORY';
            $institutionType = InstitutionType::where('name', 'observatoire')->first();
            Institution::create([
                'id' => (string)Str::uuid(),
                'slug' => Str::slug($name),
                'name' => $name,
                'acronyme' => $faker->randomLetter,
                'iso_code' => $faker->iso8601,
                'institution_type_id' => $institutionType->id
            ]);

            // create filial
            $name = 'FILIAL';
            $institutionType = InstitutionType::where('name', 'filiale')->first();
            Institution::create([
                'id' => (string)Str::uuid(),
                'slug' => Str::slug($name),
                'name' => $name,
                'acronyme' => $faker->randomLetter,
                'iso_code' => $faker->iso8601,
                'institution_type_id' => $institutionType->id
            ]);

            // create member
            $name = 'MEMBER';
            $institutionType = InstitutionType::where('name', 'membre')->first();
            Institution::create([
                'id' => (string)Str::uuid(),
                'slug' => Str::slug($name),
                'name' => $name,
                'acronyme' => $faker->randomLetter,
                'iso_code' => $faker->iso8601,
                'institution_type_id' => $institutionType->id
            ]);

        }

    }
}
