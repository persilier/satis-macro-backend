<?php

use Illuminate\Database\Seeder;

class UpdatePasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(\Satis2020\ServicePackage\Database\Seeds\UpdatePasswordSeeder::class);
    }
}
