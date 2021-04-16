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
                        'path' => 'components/background.jpg',
                        'title' => 'background.jpg'
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
