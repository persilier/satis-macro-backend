<?php

/** @var Factory $factory */
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\User;

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

$factory->define(User::class, function (Faker $faker) {

    $staff = \Satis2020\ServicePackage\Models\Staff::with('identite.user')->get()->filter(function ($value, $key) {
        return is_null($value->identite->user);
    })->random();

    return [
        'id' => (string) Str::uuid(),
        'username' => $staff->identite->email[0],
        'password' => bcrypt('123456789'),
        'identite_id' => $staff->identite->id,
        'disabled_at' => null
    ];
});
