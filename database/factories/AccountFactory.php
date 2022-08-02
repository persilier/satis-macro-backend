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

$factory->define(\Satis2020\ServicePackage\Models\Account::class, function (Faker $faker) {

    $clients = \Satis2020\ServicePackage\Models\Client::doesntHave('accounts')->get();

    return [
        'id' => (string) Str::uuid(),
        'client_id' => $clients->random()->id,
        'institution_id' => \Satis2020\ServicePackage\Models\Institution::all()->random()->id,
        'account_type_id' => \Satis2020\ServicePackage\Models\AccountType::all()->random()->id,
        'number' => $faker->bankAccountNumber,
    ];
});
