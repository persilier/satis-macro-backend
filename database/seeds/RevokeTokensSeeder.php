<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RevokeTokensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("oauth_access_tokens")->truncate();
    }
}
