<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Satis2020\ServicePackage\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Position::truncate();
        Position::flushEventListeners();
        DB::table('institution_position')->truncate();
        factory(\Satis2020\ServicePackage\Models\Position::class, 10)->create()->each(
            function ($entity) {

                $collection = collect([]);

                \Satis2020\ServicePackage\Models\Institution::all()->random(mt_rand(1, 4))->map(function ($item, $key) use ($collection) {
                    $collection->push($item->id);
                    return $item;
                });

                $entity->institutions()->attach($collection->all());
            }
        );
    }
}
