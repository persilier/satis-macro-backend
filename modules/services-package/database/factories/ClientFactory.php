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

$factory->define(\Satis2020\ServicePackage\Models\Client::class, function (Faker $faker) {

    $identites = \Satis2020\ServicePackage\Models\Identite::with('client-from-my-institution')->get()->filter(function ($value, $key) {
        return is_null($value->client);
    });

    return [
        'id' => (string) Str::uuid(),
        'type_clients_id' => \Satis2020\ServicePackage\Models\TypeClient::all()->random()->id,
        'category_clients_id' => \Satis2020\ServicePackage\Models\CategoryClient::all()->random()->id,
        'identites_id' => $identites->random()->id,
    ];
});
