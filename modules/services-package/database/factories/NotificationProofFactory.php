<?php

/** @var Factory $factory */

use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\NotificationProof;

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

$factory->define(NotificationProof::class, function (Faker $faker) {

    $channels = [NotificationConsts::SMS_CHANEL,NotificationConsts::EMAIL_CHANNEL];
    $channel = $channels[array_rand($channels, 1)];
    $to = $channel==NotificationConsts::EMAIL_CHANNEL?$faker->safeEmail:$faker->phoneNumber;
    return [
        'id' => (string) Str::uuid(),
        "channel"=>$channel,
        "to"=> Identite::query()->inRandomOrder()->first()->id,
        "sent_at"=>Carbon::today()->subDays(rand(0, 30)),
        "message"=>$faker->text,
        "institution_id"=> Institution::query()->inRandomOrder()->first()->id,
    ];
});
