<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetadataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Metadata::flushEventListeners();

        $natures = [
            ['libelle' => 'Macro', 'description' => "Il s'agit de satis macro"],
            ['libelle' => 'Hub', 'description' => "Il s'agit de satis hub"],
            ['libelle' => 'Pro', 'description' => "Il s'agit de satis pro"]
        ];

        $steps = [
            [
                'family' => 'nature',
                'title' => "Définir la nature de l'application",
                'content' => $natures,
                'name' => 1
            ],
            [
                'family' => 'register-form',
                'title' => "Définir le formulaire d'enregistrement des plaintes",
                'content' => ['name' => 'services'],
                'name' => 2
            ],
            [
                'family' => 'register-header',
                'title' => "Configurer l'affichage de la liste des institutions",
                'content' => ['name' => 'services'],
                'name' => 3
            ]
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Metadata::flushEventListeners();

//        Metadata::create([
//            'id' => (string)Str::uuid(),
//            'name' => 'app-types',
//            'data' => json_encode($natures)
//        ]);
//
//        Metadata::create([
//            'id' => (string)Str::uuid(),
//            'name' => 'installation-steps',
//            'data' => json_encode($steps)
//        ]);
//
//        Metadata::create([
//            'id' => (string)Str::uuid(),
//            'name' => 'current-step',
//            'data' => json_encode(0)
//        ]);

        Metadata::create([
            'id' => (string)Str::uuid(),
            'name' => 'app-nature',
            'data' => json_encode("")
        ]);
    }
}
