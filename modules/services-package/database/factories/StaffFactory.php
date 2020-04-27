<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Identite;

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

$factory->define(\Satis2020\ServicePackage\Models\Staff::class, function (Faker $faker) {

    $institution = \Satis2020\ServicePackage\Models\Institution::with(['units', 'positions'])->get()->filter(function ($value, $key) {
        return count($value->units) !== 0 && count($value->positions) !== 0;
    })->random();

    $sexe = $faker->randomElement(['male', 'female']);
    $identite = Identite::create([
        'firstname' => $faker->firstName($sexe),
        'lastname' => $faker->lastName,
        'sexe' => strtoupper(substr($sexe, 0, 1)),
        'telephone' => [$faker->phoneNumber],
        'email' => [$faker->safeEmail]
    ]);

    return [
        'id' => (string)Str::uuid(),
        'identite_id' => $identite->id,
        'position_id' => Arr::random($institution->positions->all())->id,
        'unit_id' => Arr::random($institution->units->all())->id,
    ];
});
