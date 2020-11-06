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

class RecurrenceNotificationsTableSeeder extends Seeder
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
                'event' => 'recurrence-alert',
                'description' => "Notification envoyée au pilote lorsque le nombre de réclamations acceptable sur une période donnée est dépassée",
                'text' => "Le nombre maximum de réclamations tolérable est dépassé"
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
