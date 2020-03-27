<?php

/** @var Factory $factory */
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
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

$factory->define(Metadata::class, function (Faker $faker) {

    return [
        'id' => (string) Str::uuid(),
        'name' => 'models',
        'data' => [
            [
                "name" => 'users',
                "description" => "model users",
                "fonctions" => "models/fonction1"
            ],
            [
                "name" => 'institutions',
                "description" => "models institutions",
                "fonctions" => "models/fonction2"
            ]
        ],
    ];
});
