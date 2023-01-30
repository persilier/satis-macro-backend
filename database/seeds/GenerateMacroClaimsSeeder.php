<?php

use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Database\Seeds\AssignClaimToStaffSeeder;
use Satis2020\ServicePackage\Database\Seeds\ClaimSeeder;
use Satis2020\ServicePackage\Database\Seeds\ClaimValidatedSeeder;

class GenerateMacroClaimsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ClaimSeeder::class);
        $this->call(AssignClaimToStaffSeeder::class);
        $this->call(ClaimValidatedSeeder::class);
    }
}
