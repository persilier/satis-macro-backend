<?php

use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Models\Claim;

class UpdateClaimsChannels extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Claim::query()
            ->chunk(1000,function ($claims){
                foreach ($claims as $claim){
                    $claim->request_channel_slug = strtolower($claim->request_channel_slug);
                    $claim->response_channel_slug = strtolower($claim->response_channel_slug);
                    $claim->save();
                }
            });
    }
}
