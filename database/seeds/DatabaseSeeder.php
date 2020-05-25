<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\MetadataTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstitutionTypeTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\CategoryClientTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\TypeClientTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\IdentiteTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\ClientTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PerformanceIndicatorTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstitutionTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\UnitTypeTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\UnitTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\AccountTypeTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\AccountTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PositionTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\StaffTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\UnitLeadSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\SeverityLevelTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\ClaimCategoryTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\ClaimObjectTableSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\UsersTableSeeder::class);
    }
}
