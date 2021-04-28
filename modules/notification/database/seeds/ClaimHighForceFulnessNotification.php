<?php

namespace Satis2020\Notification\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use stdClass;

/**
 * Class ClaimHighForceFulnessNotification
 * @package Satis2020\Notification\Database\Seeds
 */
class ClaimHighForceFulnessNotification extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $eventData = json_decode(Metadata::ofName('notifications')->firstOrFail()->data);

        $events = [
            [
                'event' => 'register-a-claim-high-force-fulness',
                'description' => "Notification envoyée au pilote après enregistrement d'une réclamation de gravité élevé.",
                'text' => "Une nouvelle reclamation a ete enregistree. Reference: {claim_reference}. Gravite : {severity_level}. Etat : {claim_status}. Objet : {claim_object}"
            ]
        ];

        foreach ($events as $event){
            $object = new stdClass();
            foreach (['event', 'description', 'text'] as $key){
                $object->$key = $event[$key];
            }
            array_push($eventData, $object);
        }

        Metadata::where('name', 'notifications')->first()->update(['data' => json_encode($eventData)]);

    }
}
