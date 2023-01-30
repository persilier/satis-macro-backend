<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Channel;

class InitializeChannelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Channel::truncate();

        $channels = [
            ['slug' => 'sms', 'name' => 'SMS', 'is_editable' => 0, 'is_response' => 1],
            ['slug' => 'telephone', 'name' => 'APPEL TELEPHONIQUE', 'is_editable' => 0, 'is_response' => 0],
            ['slug' => 'email', 'name' => 'EMAIL', 'is_editable' => 0, 'is_response' => 1],
            ['slug' => 'web', 'name' => 'WEB', 'is_editable' => 0, 'is_response' => 0],
            ['slug' => 'mobile', 'name' => 'MOBILE', 'is_editable' => 0, 'is_response' => 0],
            ['slug' => 'entretien', 'name' => 'ENTRETIEN', 'is_editable' => 0, 'is_response' => 0]
        ];

        foreach ($channels as $channel) {
            if (Channel::where('name', $channel['name'])->doesntExist()) {
                Channel::create($channel);
            }
        }

    }
}
