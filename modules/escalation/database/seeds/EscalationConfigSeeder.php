<?php

namespace Satis2020\Escalation\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\Escalation\Repositories\TreatmentBoardRepository;
use Satis2020\Escalation\Services\EscalationConfigService;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EscalationConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $treatmentBoardRepo = new TreatmentBoardRepository;

        if ($treatmentBoardRepo->getStandardBoard()==null){
            $escalationSettings = [
                'standard_bord_exists' => true,
                'specific_bord_exists' => false,
            ];

            TreatmentBoard::query()
                ->create(
                    [
                        'name'=>"Standard",
                        'type' =>TreatmentBoard::STANDARD,
                        'description'=>"Standard",
                        'created_by' =>null,
                        'institution_id' =>Institution::query()->first()->id
                    ]);

            Metadata::query()->updateOrCreate([
                "name"=>Metadata::ESCALATION
            ],[
                'id' => (string)Str::uuid(),
                'name' => Metadata::ESCALATION,
                'data' => json_encode($escalationSettings)
            ]);
        }
    }
}
