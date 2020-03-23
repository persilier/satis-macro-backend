<?php
use Illuminate\Database\Seeder;
class ServicePackageSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(Satis2020\ServicePackage\database\seeds\MetadataTableSeeder::class);
    }
}

