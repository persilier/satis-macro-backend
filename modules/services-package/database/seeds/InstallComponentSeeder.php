<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Models\Channel;

class InstallComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $appNature = Config::get('services.app_nature', 'PRO');

        $components = [
            [
                'name' => 'connection',
                'description' => 'Interface de Connexion',
                'params' => [
                    'logo' => [
                        'type' => 'image',
                        'path' => 'components/logo.png',
                        'title' => 'logo.png'
                    ],
                    'owner_logo' => [
                        'type' => 'image',
                        'path' => 'components/owner-logo.png',
                        'title' => 'owner-logo.png'
                    ],
                    'background' => [
                        'type' => 'image',
                        'path' => 'components/background.jpeg',
                        'title' => 'background.jpeg'
                    ],
                    'title' => [
                        'type' => 'text',
                        'value' => $appNature == 'MACRO' ? 'SATISMACRO' : ($appNature == 'HUB' ? 'SATISHUB' : 'SATISPRO'),
                    ],
                    'description' => [
                        'type' => 'text',
                        'value' => 'Votre nouvel outil de gestion des réclamations',
                    ],
                    'version' => [
                        'type' => 'text',
                        'value' => '2020.1',
                    ],
                    'header' => [
                        'type' => 'text',
                        'value' => 'Bienvenue sur SATIS',
                    ]
                ]
            ],
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
            [
                'name' => 'register_claim',
                'description' => 'Configuration des libellé du formulaire d\'enregistrement de plaintes',
                'params' => [
                    'info_page' => [
                        'type' => 'text',
                        'value' => "Formulaire d'enregistrement d'une réclamation. Utilisez ce formulaire pour enregistrer les réclamations de vos clients.",
                    ],
                    'title' => [
                        'type' => 'text',
                        'value' => "Enregistrement réclamation",
                    ],
                    'telecharger' => [
                        'type' => 'text',
                        'value' => "Télécharger format",
                    ],
                    'importer' => [
                        'type' => 'text',
                        'value' => "Importer Réclamations",
                    ],
                    'institution' => [
                        'type' => 'text',
                        'value' => "Institution concernée",
                    ],
                    'institution_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner l'institution concernée",
                    ],
                    'info_cible' => [
                        'type' => 'text',
                        'value' => "Client",
                    ],
                    'nom' => [
                        'type' => 'text',
                        'value' => "Nom",
                    ],
                    'nom_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez entrer le nom de famille",
                    ],
                    'prenoms' => [
                        'type' => 'text',
                        'value' => "Prénom(s)",
                    ],
                    'prenoms_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez entrer le prénom",
                    ],
                    'sexe' => [
                        'type' => 'text',
                        'value' => "Sexe",
                    ],
                    'sexe_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner le sexe",
                    ],
                    'ville' => [
                        'type' => 'text',
                        'value' => "Ville",
                    ],
                    'ville_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez entrer la ville",
                    ],
                    'telephone' => [
                        'type' => 'text',
                        'value' => "Téléphone(s)",
                    ],
                    'telephone_placeholder' => [
                        'type' => 'text',
                        'value' => "Numéro(s)",
                    ],
                    'email' => [
                        'type' => 'text',
                        'value' => "Email(s)",
                    ],
                    'email_placeholder' => [
                        'type' => 'text',
                        'value' => "Email(s)",
                    ],
                    'info_reclamation' => [
                        'type' => 'text',
                        'value' => "Réclamation :",
                    ],
                    'canal_reception' => [
                        'type' => 'text',
                        'value' => "Canal de réception",
                    ],
                    'canal_reception_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner le canal de réception",
                    ],
                    'canal_reponse' => [
                        'type' => 'text',
                        'value' => "Canal de réponse",
                    ],
                    'canal_reponse_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner le canal de réponse",
                    ],
                    'categorie' => [
                        'type' => 'text',
                        'value' => "Catégorie de réclamation",
                    ],
                    'categorie_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner la catégorie de réclamation",
                    ],
                    'object' => [
                        'type' => 'text',
                        'value' => "Object de réclamation",
                    ],
                    'object_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner l'objet de réclamation",
                    ],
                    'montant' => [
                        'type' => 'text',
                        'value' => "Montant réclamé",
                    ],
                    'montant_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez entrer le montant réclamé",
                    ],
                    'devise' => [
                        'type' => 'text',
                        'value' => "Devise du montant réclamé",
                    ],
                    'devise_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner la devise du montant réclamé",
                    ],
                    'date' => [
                        'type' => 'text',
                        'value' => "Date de l'événement",
                    ],
                    'date_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez entrer la date de l'événement",
                    ],
                    'lieu' => [
                        'type' => 'text',
                        'value' => "Lieu de l'événement",
                    ],
                    'lieu_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez entrer le lieu de l'événement",
                    ],
                    'relation' => [
                        'type' => 'text',
                        'value' => "Relation",
                    ],
                    'relation_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner la relation avec le client",
                    ],
                    'piece' => [
                        'type' => 'text',
                        'value' => "Pièces jointes",
                    ],
                    'piece_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner le(s) fichier(s)",
                    ],
                    'unite' => [
                        'type' => 'text',
                        'value' => "Unité concernée",
                    ],
                    'unite_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner l'unité concernée",
                    ],
                    'compte' => [
                        'type' => 'text',
                        'value' => "Numéro de compte concerné",
                    ],
                    'compte_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez sélectionner le compte concerné",
                    ],
                    'description' => [
                        'type' => 'text',
                        'value' => "Description",
                    ],
                    'description_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez entrer la description",
                    ],
                    'attente' => [
                        'type' => 'text',
                        'value' => "Attente",
                    ],
                    'attente_placeholder' => [
                        'type' => 'text',
                        'value' => "Veuillez entrer l'attente du réclamant",
                    ],
                    'last_titre' => [
                        'type' => 'text',
                        'value' => "Relance",
                    ],
                    'question' => [
                        'type' => 'text',
                        'value' => "Est-ce une relance ?",
                    ],
                    'reponse_oui' => [
                        'type' => 'text',
                        'value' => "Oui",
                    ],
                    'reponse_non' => [
                        'type' => 'text',
                        'value' => "Non",
                    ]
                ]
            ],
        ];

        foreach ($components as $component) {

            $componentModel = \Satis2020\ServicePackage\Models\Component::create(['name' => $component['name'], 'description' => $component['description']]);

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
