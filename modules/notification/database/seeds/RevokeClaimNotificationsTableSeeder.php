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

class RevokeClaimNotificationsTableSeeder extends Seeder
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
                'event' => 'revoke-claim-claimer-notification',
                'description' => "Notification envoyée au réclamant après la révocation de sa réclamation",
                'text' => "Nous vous informons que votre réclamation : Reference: {claim_reference}. Objet : {claim_object} a été révoquée avec succès. Si vous n'êtes pas à l'origine de cette action, veuillez nous contacter"
            ],
            [
                'event' => 'revoke-claim-staff-notification',
                'description' => "Notification envoyée aux staff concerné après la révocation d'une réclamation",
                'text' => "Nous vous informons que la réclamation : Reference: {claim_reference}. Objet : {claim_object} vient d'être révoquée."
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
