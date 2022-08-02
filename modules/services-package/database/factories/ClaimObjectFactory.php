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

$factory->define(\Satis2020\ServicePackage\Models\ClaimObject::class, function (Faker $faker) {

    return [
        'id' => (string) Str::uuid(),
        'name' => $faker->word,
        'description' => $faker->text,
        'claim_category_id' => \Satis2020\ServicePackage\Models\ClaimCategory::all()->random()->id,
        'severity_levels_id' => \Satis2020\ServicePackage\Models\SeverityLevel::all()->random()->id,
        'time_limit' => $faker->numberBetween(1, 7)
    ];
});
