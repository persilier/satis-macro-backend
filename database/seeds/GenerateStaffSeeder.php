<?php

use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Database\Seeds\MacroStaffSeeder;

class GenerateStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(MacroStaffSeeder::class);
    }
}
