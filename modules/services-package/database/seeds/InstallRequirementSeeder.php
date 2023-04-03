<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Satis2020\ServicePackage\Models\Requirement;

class InstallRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $requirements = [
            [
                'id' => (string)Str::uuid(),
                'name' => 'description',
                'description' => "La description de la réclamation"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'relationship_id',
                'description' => "La nature de la relation qui existe entre le réclamant et l'institution visée par la réclamation"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'account_targeted_id',
                'description' => "Le numéro du compte concerné"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'institution_targeted_id',
                'description' => "L'institution visée"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'unit_targeted_id',
                'description' => "L'unité visée"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'event_occured_at',
                'description' => "Date de survenue de l'incident"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'lieu',
                'description' => "Le lieu de survenue de l'incident"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'claimer_expectation',
                'description' => "Les attentes du réclamant"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'amount_disputed',
                'description' => "Le montant réclamé"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'amount_currency_slug',
                'description' => "La devise du montant réclamé"
            ],
            [
                'id' => (string)Str::uuid(),
                'name' => 'file',
                'description' => "Piece jointe"
            ]
        ];

        $appNature = Config::get('services.app_nature', 'PRO');

        foreach ($requirements as $requirement) {
            if ((($requirement['name'] == 'relationship_id' && $appNature == 'HUB') || $requirement['name'] != 'relationship_id') && !Requirement::where('name', $requirement['name'])->exists())
                Requirement::create($requirement);
            else if (Requirement::where('name', $requirement['name'])->exists()) {
                Requirement::where('name', $requirement['name'])->update([
                    'description' => [
                        App::getLocale() => $requirement['description']
                    ],
                ]);
            }
        }
    }
}
