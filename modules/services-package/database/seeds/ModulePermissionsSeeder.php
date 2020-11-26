<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Module;
use Spatie\Permission\Models\Permission;

/**
 * Class ModulePermissionsSeeder
 * @package Satis2020\ServicePackage\Database\Seeds
 */
class ModulePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nature = env('APP_NATURE');
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('permissions')->where('module_id', '!=', NULL)->update(['module_id' => NULL]);

        Module::truncate();
        Module::flushEventListeners();
        $faker = Faker::create();

        for($i = 0; $i < 5; $i++):

            Module::create([
                'id' => (string)Str::uuid(),
                'name' => $faker->name,
                'description' => $faker->sentence
            ]);

        endfor;

        Permission::all()->map(function($permission){
            $permission->update(['module_id' => Module::all()->random()->id]);
        });
    }
}
