<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Identite;

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

$factory->define(\Satis2020\ServicePackage\Models\Staff::class, function (Faker $faker) {

    $app_nature = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'app-nature')->first()->data);

    $institution = $app_nature === "hub"
        ? \Satis2020\ServicePackage\Models\Institution::all()->random()
        : \Satis2020\ServicePackage\Models\Institution::with(['units', 'positions'])->get()->filter(function ($value, $key) {
            return count($value->units) !== 0 && count($value->positions) !== 0;
        })->random();

    $unit = null;
    if ($app_nature === "hub" && \Satis2020\ServicePackage\Models\Unit::all()->count() > 0) {
        $unit = \Satis2020\ServicePackage\Models\Unit::all()->random();
    } elseif ($app_nature !== "hub") {
        $unit = Arr::random($institution->units->all());
    }

    $identites = \Satis2020\ServicePackage\Models\Identite::with('staff')->get()->filter(function ($value, $key) {
        return is_null($value->staff);
    });

    return [
        'id' => (string)Str::uuid(),
        'identite_id' => $identites->random()->id,
        'position_id' => $app_nature === "hub" ? \Satis2020\ServicePackage\Models\Position::all()->random()->id : Arr::random($institution->positions->all())->id,
        'institution_id' => $institution->id,
        'unit_id' => is_null($unit)
            ? null
            : $unit->id,
    ];
});
