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

$factory->define(\Satis2020\ServicePackage\Models\Identite::class, function (Faker $faker) {

    $sexe = $faker->randomElement(['male', 'female']);

    return [
        'id' => (string) Str::uuid(),
        'firstname' => $faker->firstName($sexe),
        'lastname' => $faker->lastName,
        'sexe' => strtoupper(substr($sexe, 0, 1)),
        'telephone' => [$faker->phoneNumber],
        'email' => [$faker->safeEmail]
    ];
});
