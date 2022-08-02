<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Component;

class InitializeComponentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Component::truncate();

        $items = [
            [
                'name' => 'connection',
                'description' => 'Page de connexion',
                'params' => json_encode([
                    'logo' => [
                        'value' => '',
                        'type' => 'image'
                    ],
                    'title' => [
                        'value' => 'Updated title',
                        'type' => 'text'
                    ],
                    'description' => [
                        'value' => 'Updated description',
                        'type' => 'text'
                    ],
                    'background' => [
                        'value' => '',
                        'type' => 'image'
                    ],
                    'version' => [
                        'value' => 'Updated version',
                        'type' => 'text'
                    ]
                ])
            ]
        ];

        foreach ($items as $item) {
            if (Component::where('name', $item['name'])->doesntExist()) {
                Component::create($item);
            }
        }

    }
}
