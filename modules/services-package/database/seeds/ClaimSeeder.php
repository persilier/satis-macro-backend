<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClaimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        //Claim::flushEventListeners();
        factory(Claim::class, 1000)->create();
        $units = Unit::with('institution.institutionType')
            ->whereHas('institution.institutionType',function ($query){
                $query->where('name','<>','holding');
            })
            ->get();

        foreach ($units as $unit)
        {
            $this->command->info(" Génération des réclamation pour l'institution {$unit->name} cours....");
            factory(Claim::class, 10)->create(['institution_targeted_id' => $unit->institution_id]);
            $this->command->info(" Génération des réclamation pour terminée !");
        }
    }
}
