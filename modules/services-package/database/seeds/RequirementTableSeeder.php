<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Requirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequirementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Requirement::truncate();
        Requirement::flushEventListeners();

        $claimObjects = ClaimObject::all()->pluck('id');

        $requirement = Requirement::create([
            'id' => (string)Str::uuid(),
            'name' => 'description',
            'description' => "La description de la réclamation"
        ]);

        $requirement->claimObjects()->attach($claimObjects->random(4)->all());

        $requirement = Requirement::create([
            'id' => (string)Str::uuid(),
            'name' => 'relationship_id',
            'description' => "La nature de la relation qui existe entre le réclamant et l'institution visée par la réclamation"
        ]);

        $requirement->claimObjects()->attach($claimObjects->random(4)->all());

        $requirement = Requirement::create([
            'id' => (string)Str::uuid(),
            'name' => 'account_targeted_id',
            'description' => "Le numéro du compte concerné"
        ]);

        $requirement->claimObjects()->attach($claimObjects->random(4)->all());

        $requirement = Requirement::create([
            'id' => (string)Str::uuid(),
            'name' => 'institution_targeted_id',
            'description' => "L'institution visée"
        ]);

        $requirement->claimObjects()->attach($claimObjects->random(4)->all());

        $requirement = Requirement::create([
            'id' => (string)Str::uuid(),
            'name' => 'unit_targeted_id',
            'description' => "L'unité visée"
        ]);

        $requirement->claimObjects()->attach($claimObjects->random(4)->all());

        $requirement = Requirement::create([
            'id' => (string)Str::uuid(),
            'name' => 'event_occured_at',
            'description' => "Date de survenue de l'incident"
        ]);

        $requirement->claimObjects()->attach($claimObjects->random(4)->all());

        $requirement = Requirement::create([
            'id' => (string)Str::uuid(),
            'name' => 'claimer_expectation',
            'description' => "Les attentes du réclamant"
        ]);

        $requirement->claimObjects()->attach($claimObjects->random(4)->all());

        $requirement = Requirement::create([
            'id' => (string)Str::uuid(),
            'name' => 'amount_disputed',
            'description' => "Le montant réclamé"
        ]);

        $requirement->claimObjects()->attach($claimObjects->random(4)->all());

        $requirement = Requirement::create([
            'id' => (string)Str::uuid(),
            'name' => 'amount_currency_slug',
            'description' => "La devise du montant réclamé"
        ]);

        $requirement->claimObjects()->attach($claimObjects->random(4)->all());

    }
}
