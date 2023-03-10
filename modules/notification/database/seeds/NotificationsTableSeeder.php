<?php

namespace Satis2020\Notification\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Metadata::updateOrCreate(
            [
                'name' => 'notifications',
            ],
            [
                'id' => (string)Str::uuid(),
                'data' => json_encode([
                    [
                        'event' => 'acknowledgment-of-receipt',
                        'description' => "Notification envoyée au réclamant après enregistrement d'une réclamation",
                        'text' => "Votre reclamation a ete enregistree avec succes. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'register-a-claim',
                        'description' => "Notification envoyée au pilote après enregistrement d'une réclamation",
                        'text' => "Une nouvelle reclamation a ete enregistree. Reference: {claim_reference}. Etat : {claim_status}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'complete-a-claim',
                        'description' => "Notification envoyée au pilote après completion d'une réclamation",
                        'text' => "Une reclamation a ete completee. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'transferred-to-targeted-institution',
                        'description' => "Notification envoyée au pilote après transfert d'une réclamation à l'institution visée",
                        'text' => "Une reclamation a ete transferee a votre institution. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'transferred-to-unit',
                        'description' => "Notification envoyée aux staff après transfert d'une réclamation à une unité de traitement",
                        'text' => "Une reclamation a ete transferee a votre unite. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'assigned-to-staff',
                        'description' => "Notification envoyée à un staff après qu'on lui ai affecté une réclamation",
                        'text' => "Une reclamation vient de vous etre assigne. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'reject-a-claim',
                        'description' => "Notification envoyée au pilote et aux staff après rejet d'une réclamation précedemment transférée à une unité de traitement",
                        'text' => "Rejet d'une reclamtion. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'treat-a-claim',
                        'description' => "Notification envoyée au pilote après traitement d'une réclamation",
                        'text' => "{responsible_staff} vient de traiter une reclamation. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'invalidate-a-treatment',
                        'description' => "Notification envoyée à un staff après invalidation de son traitement",
                        'text' => "Invalidation de votre traitement. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'validate-a-treatment',
                        'description' => "Notification envoyée à un staff après validation de son traitement",
                        'text' => "Validation de votre traitement. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'communicate-the-solution',
                        'description' => "Notification envoyée au réclamant après validation du traitement de sa réclamation",
                        'text' => "{solution_communicated}. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'communicate-the-solution-unfounded',
                        'description' => "Notification envoyée au réclamant après validation du non fondé de sa réclamation",
                        'text' => "{solution_communicated}. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'add-contributor-to-discussion',
                        'description' => "Notification envoyée à un intervenant après son ajout dans une discussion",
                        'text' => "{created_by} vous a ajoute dans {discussion_name}. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'post-discussion-message',
                        'description' => "Notification envoyée aux intervenants après ajout d'un message dans une discussion",
                        'text' => "{posted_by} a écrit dans {discussion_name}. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'transferred-to-unit-escalation',
                        'description' => "Notification envoyée aux staff après transfert d'une réclamation à une unité ou comité de traitement dans le processus d'escalade",
                        'text' => "Une réclamation a été transferée dans le comité d'ont vous êtes membre. Reference: {claim_reference}. Objet : {claim_object}"
                    ],
                    [
                        'event' => 'treat-a-claim-escalation',
                        'description' => "Notification envoyée au pilote après traitement d'une réclamation dans le processus d'escalade",
                        'text' => "{responsible_staff} vient de traiter une reclamation. Reference: {claim_reference}. Objet : {claim_object}"
                    ]
                ])
            ]
        );
    }
}
