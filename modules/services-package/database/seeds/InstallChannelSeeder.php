<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Models\Channel;

class InstallChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $channels = [
            ['slug' => 'sms', 'name' => 'SMS',
                'is_editable' => 0, 'is_response' => 1],
            ['slug' => 'telephone', 'name' => 'APPEL TELEPHONIQUE',
                'is_editable' => 0, 'is_response' => 0],
            ['slug' => 'email', 'name' => 'EMAIL',
                'is_editable' => 0, 'is_response' => 1],
            ['slug' => 'web', 'name' => 'WEB',
                'is_editable' => 0, 'is_response' => 0],
            ['slug' => 'mobile', 'name' => 'MOBILE',
                'is_editable' => 0, 'is_response' => 0],
            ['slug' => 'entretien', 'name' => 'ENTRETIEN',
                'is_editable' => 0, 'is_response' => 0],
        ];

        foreach ($channels as $channel) {
            Channel::create($channel);
        }

    }
}
