<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Models\Metadata;

/*
|--------------------------------------------------------------------------
| Metadata Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\Satis2020\ServicePackage\Models\Institution::class, function (Faker $faker) {

    $name = $faker->word;
    $app_nature = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'app-nature')->first()->data);
    $institutionType = $app_nature === "hub"
        ? InstitutionType::where('name', 'membre')->first()
        : InstitutionType::where('name', 'filiale')->first();

    return [
        'id' => (string)Str::uuid(),
        'slug' => Str::slug($name),
        'name' => $name,
        'acronyme' => $faker->randomLetter,
        'iso_code' => $faker->iso8601,
        'institution_type_id' => $institutionType->id
    ];

});
