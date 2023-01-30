<?php

namespace Satis2020\Channel\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Channel;

class ChannelsTableSeeder extends Seeder
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
        Channel::flushEventListeners();

        Channel::create([
            'id' => (string)Str::uuid(),
            'name' => 'SMS',
            'slug' => 'sms',
            'is_editable' => false,
            'is_response' => true,
        ]);

        Channel::create([
            'id' => (string)Str::uuid(),
            'name' => 'EMAIL',
            'slug' => 'email',
            'is_editable' => false,
            'is_response' => true,
        ]);

        Channel::create([
            'id' => (string)Str::uuid(),
            'name' => 'TELEPHONE',
            'slug' => 'telephone',
            'is_editable' => false,
            'is_response' => true,
        ]);

        Channel::create([
            'id' => (string)Str::uuid(),
            'name' => 'ENTRETIEN',
            'slug' => 'entretien',
            'is_editable' => false,
            'is_response' => true,
        ]);

    }
}
