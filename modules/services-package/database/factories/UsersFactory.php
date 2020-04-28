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
    $staff = Staff::all()->random();
    return [
        'id' => (string) Str::uuid(),
        'username' => $staff->identite->email[0],
        'password' => bcrypt('123456789'),
        'remember_token' => Str::random(10),
        'verified' => $verified = $faker->randomElement([User::UNVERIFIED_USER, User::VERIFIED_USER]),
        'verification_token' => $verified == User::VERIFIED_USER ? null : User::generateVerificationToken(),
        'identite_id' => $staff->identite->id,
    ];
});
