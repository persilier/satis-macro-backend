<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Metadata::truncate();
        Metadata::flushEventListeners();


        $authSettings = [
            'inactivity_control' => true,
            'inactivity_time_limit' => 30, //30 days
            'password_expiration_control' => true,
            'password_lifetime' => 90, //90 days
            'max_password_histories' => 12,
            'password_notif_delay' => 14, //14 days
            'password_notif_msg' => "Votre mot de passe arrive à expiration dans 14 jours, pensez à le renouveler.",
            'password_expiration_msg' => "Votre mot de passe a expiré",
            'block_attempt_control' => true,
            'max_attempt' => 3,
            'attempt_delay' => 15, //15 minutes
            'attempt_waiting_time' => 60, //15 minutes
            'account_blocked_msg' => "Votre compte a été bloqué suite à de nombreuses tentative manqué",
        ];

        Metadata::create([
            'id' => (string)Str::uuid(),
            'name' => Metadata::AUTH_PARAMETERS,
            'data' => json_encode($authSettings)
        ]);

    }
}
