<?php

use Illuminate\Database\Seeder;

class InstallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallChannelSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallComponentSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallInstitutionTypeSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallInstitutionSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallMetadataSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsHoldingSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsFilialSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsMembreSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsObservatorySeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsIndependantSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PermissionsInstitutionTypesSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallRequirementSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallSeverityLevelSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallAdministratorSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\ReportingTitlesSeeder::class);
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\MetadataTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\ProxyConfigSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\AuthConfigSeeder::class);
        
        


    }
}
