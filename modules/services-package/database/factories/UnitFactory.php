<?php

/** @var Factory $factory */
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Metadata Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\Satis2020\ServicePackage\Models\Unit::class, function (Faker $faker) {

    $units = \Satis2020\ServicePackage\Models\Unit::with('unitType.child')->get()->filter(function ($value, $key) {
        return !is_null($value->unitType->children);
    });

    $parent = $units->count() > 0
        ? $units->random()
        : null;

    $app_nature = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'app-nature')->first()->data);

    return [
        'id' => (string) Str::uuid(),
        'name' => $faker->word,
        'description' => $faker->text,
        'unit_type_id' => is_null($parent) ? \Satis2020\ServicePackage\Models\UnitType::whereNull('parent_id')->first()->id : Arr::random($parent->unitType->children)->id,
        'parent_id' => is_null($parent) ? null : $parent->id,
        'institution_id' => $app_nature === 'hub' ? null : \Satis2020\ServicePackage\Models\Institution::all()->random()->id
    ];
});
