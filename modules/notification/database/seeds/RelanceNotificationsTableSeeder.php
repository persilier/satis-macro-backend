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

class RelanceNotificationsTableSeeder extends Seeder
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
                'event' => 'reminder-before-deadline',
                'description' => "Notification envoyée aux personnes concernées avant expiration du délai de résolution de la réclamation",
                'text' => "Le délai de traitement de la réclamation : Reference: {claim_reference}. Objet : {claim_object} expire dans {time_before}"
            ],
            [
                'event' => 'reminder-after-deadline',
                'description' => "Notification envoyée aux personnes concernées après expiration du délai de résolution de la réclamation",
                'text' => "Le délai de traitement de la réclamation : Reference: {claim_reference}. Objet : {claim_object} a expiré depuis {time_after}"
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
