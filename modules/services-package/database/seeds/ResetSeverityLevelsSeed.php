<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\SeverityLevel;

/**
 * Class ResetSeverityLevelsSeed
 * @package Satis2020\ServicePackage\Database\Seeds
 */
class ResetSeverityLevelsSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('claim_objects')->where('severity_levels_id', '!=', NULL)->update(['severity_levels_id' => NULL]);
        SeverityLevel::truncate();
        SeverityLevel::flushEventListeners();

        $severityLevels = [

            ['id' => (string)Str::uuid(), 'name' => 'Faible', "description" => "Niveau de gravité faible", 'status' => 'low', 'color' => "#008000"],
            ['id' => (string)Str::uuid(), 'name' => 'Moyen', "description" => "Niveau de gravité moyen", 'status' => 'medium', 'color' => "#FFA500"],
            ['id' => (string)Str::uuid(), 'name' => 'Elevé', "description" => "Niveau de gravité élevé", 'status' => 'high', 'color' => "#FF0000"],

        ];


        foreach ($severityLevels as $value){

            SeverityLevel::create($value);

        }

        $claimObjects = ClaimObject::all();

        foreach ($claimObjects as $value){

            $value->update(["severity_levels_id" => SeverityLevel::all()->random()->id]);

        }
    }
}
