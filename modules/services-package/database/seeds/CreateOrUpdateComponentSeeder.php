<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Models\Component;

/**
 * Class ComponentDashboardSeeder
 * @package Satis2020\ServicePackage\Database\Seeds
 */
class CreateOrUpdateComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $components = [
            [
                'name' => 'dashboard-text',
                'description' => 'Parametrage des textes et couleurs du dashboard.',
                'params' => [
                    'title_all_institution' => [
                        'type' => 'text',
                        'value' => 'Statistiques des Réclamations de toutes les Institutions sur les 30 derniers jours'
                    ],
                    'total_enreg' => [
                        'type' => 'text',
                        'value' => 'Total Réclamations Enregistrées',
                    ],
                    'total_incomplete' => [
                        'type' => 'text',
                        'value' => 'Total Réclamations Incomplètes',
                    ],
                    'total_complet' => [
                        'type' => 'text',
                        'value' => 'Total Réclamations Complètes',
                    ],
                    'total_to_unit' => [
                        'type' => 'text',
                        'value' => 'Total Réclamations Transférées à une Unité',
                    ],
                    'total_in_treatment' => [
                        'type' => 'text',
                        'value' => 'Total Réclamations en Cours de Traitement',
                    ],
                    'total_treat' => [
                        'type' => 'text',
                        'value' => 'Total Réclamations Traitées',
                    ],
                    'total_unfound' => [
                        'type' => 'text',
                        'value' => 'Total Réclamations Non Fondées',
                    ],
                    'total_satisfated' => [
                        'type' => 'text',
                        'value' => 'Total Satisfaction Mesurée',
                    ],
                    'title_my_institution' => [
                        'type' => 'text',
                        'value' => 'Statistiques des Réclamations de mon Institution sur les 30 derniers jours',
                    ],
                    'title_activity' => [
                        'type' => 'text',
                        'value' => 'Statistiques des Réclamations des Activités sur les 30 derniers jours',
                    ],
                    'title_unit' => [
                        'type' => 'text',
                        'value' => 'Statistiques des Réclamations de mon Unité sur les 30 derniers jours',
                    ],
                    'total_by_channel' => [
                        'type' => 'text',
                        'value' => 'Total des Réclamations reçues par Canal sur les 30 derniers jours',
                    ],
                    'stat_object' => [
                        'type' => 'text',
                        'value' => 'Statistique des cinq (05) plus fréquents Objets de Réclamations sur les 30 derniers jours',
                    ],
                    'satisfaction_chart' => [
                        'type' => 'text',
                        'value' => 'Evolution de la satisfaction des réclamants sur les 11 derniers mois',
                    ],
                    'title_satisfaction_of_process' => [
                        'type' => 'text',
                        'value' => 'Evolution de la satisfaction des réclamations sur les 11 derniers mois',
                    ],
                    'title_stat_institution' => [
                        'type' => 'text',
                        'value' => 'Statistique des institutions qui reçoivent plus de réclamations sur les 30 derniers jours',
                    ],
                    'title_stat_service' => [
                        'type' => 'text',
                        'value' => 'Statistique les services techniques qui reçoivent plus de réclamations sur les 30 derniers jours',
                    ],
                    'pourcent_incomplet' => [
                        'type' => 'text',
                        'value' => '% Réclamations Incomplètes',
                    ],
                    'pourcent_complete' => [
                        'type' => 'text',
                        'value' => '% Réclamations Complètes',
                    ],
                    'pourcent_to_unit' => [
                        'type' => 'text',
                        'value' => '% Réclamations Transférées à une Unité',
                    ],
                    'pourcent_in_treatment' => [
                        'type' => 'text',
                        'value' => '% Réclamations en Cours de Traitement',
                    ],
                    'pourcent_treat' => [
                        'type' => 'text',
                        'value' => '% Réclamations Traitées',
                    ],
                    'pourcent_unfound' => [
                        'type' => 'text',
                        'value' => '% Réclamations Non Fondées',
                    ],
                    'pourcent_satisfated' => [
                        'type' => 'text',
                        'value' => '% Satisfaction Mesurée',
                    ]
                ]
            ],
        ];

        foreach ($components as $component) {

            $componentModel = Component::updateOrCreate(['name' => $component['name']], ['description' => $component['description']]
            );

            foreach ($component['params'] as $attr => $attrConfig) {

                try {

                    if ($attrConfig['type'] == 'image' && Storage::disk('public')->exists($attrConfig['path'])) {
                        $file = $componentModel->files()->create(['title' => $attrConfig['title'], 'url' => '/storage/' . $attrConfig['path']]);
                        unset($component['params'][$attr]['title']);
                        unset($component['params'][$attr]['path']);
                        $component['params'][$attr]['value'] = $file->id;
                    }

                } catch (\Exception $exception) {
                }

            }

            $componentModel->update(['params' => json_encode($component['params'])]);
        }

    }
}
