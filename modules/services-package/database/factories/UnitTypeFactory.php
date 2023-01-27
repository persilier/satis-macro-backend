<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

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

$factory->define(\Satis2020\ServicePackage\Models\UnitType::class, function (Faker $faker) {

    $unitTypes = \Satis2020\ServicePackage\Models\UnitType::all();

    $parent_id = $unitTypes->count() > 0 && $faker->randomElement([true, false])
        ? $unitTypes->random()->id
        : null;

    return [
        'id' => (string)Str::uuid(),
        'name' => $faker->word,
        'description' => $faker->text,
        'parent_id' => $parent_id
    ];
});
