<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Models\Channel;

class VerifyChannelsWhichCanBeTargetedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $channels = Channel::query()
            ->whereIn('slug', ['sms', 'email'])
            ->get(['id', 'can_be_response']);

        foreach ($channels as $channel) {
            if (!$channel->can_be_response) {
                $channel->update(['can_be_response' => 1]);
            }
        }
    }
}
