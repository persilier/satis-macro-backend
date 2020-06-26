<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StaffSeeder extends Seeder
{

    public function createIdentity()
    {
        $faker = Faker::create();

        $sexe = $faker->randomElement(['male', 'female']);

        return $identity = Identite::create([
            'id' => (string)Str::uuid(),
            'firstname' => $faker->firstName($sexe),
            'lastname' => $faker->lastName,
            'sexe' => strtoupper(substr($sexe, 0, 1)),
            'telephone' => [$faker->phoneNumber],
            'email' => [$faker->safeEmail]
        ]);
    }

    public function createUser($identity)
    {
        return $user = User::create([
            'id' => (string)Str::uuid(),
            'username' => $identity->email[0],
            'password' => bcrypt('123456789'),
            'identite_id' => $identity->id,
            'disabled_at' => null
        ]);
    }

    public function createStaff($unit, $roleName = 'collector')
    {
        $identity = $this->createIdentity();

        if ($roleName == 'collector') {
            if ($unit->institution->institutionType->holding == 'holding') {
                $roleName = 'collector-holding';
            }
            if ($unit->institution->institutionType->holding == 'observatoire') {
                $roleName = 'collector-observatory';
            }
        }
        $role = Role::where('name', $roleName)->where('guard_name', 'api')->firstOrFail();

        $user = $this->createUser($identity);
        $user->assignRole($role);

        return $staff = Staff::create([
            'id' => (string)Str::uuid(),
            'identite_id' => $identity->id,
            'position_id' => \Satis2020\ServicePackage\Models\Position::all()->random()->id,
            'institution_id' => $unit->institution->id,
            'unit_id' => $unit->id
        ]);
    }

    public function attachUnitToClaimObject($claimObject, $unit)
    {
        if (!DB::table('claim_object_unit')
            ->where('claim_object_id', $claimObject->id)
            ->where('unit_id', $unit->id)
            ->exists()) {
            $claimObject->units()->attach($unit);
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Claim::truncate();
        Claim::flushEventListeners();

        $faker = Faker::create();

        $units = Unit::with('institution.institutionType')->get();

        foreach ($units as $unit) {
            // register two staff
            $staffCollector = $this->createStaff($unit, 'collector');
            $staffCaterer = $this->createStaff($unit, 'caterer');

            // select unit lead
            $unit->update(['lead_id' => $staffCollector->id]);

            // register the claim
            $claimer = $this->createIdentity();
            $claimObject = \Satis2020\ServicePackage\Models\ClaimObject::all()->random();
            $this->attachUnitToClaimObject($claimObject, $unit);
            $claim = Claim::create([
                'id' => (string)Str::uuid(),
                'description' => $faker->text,
                'claim_object_id' => $claimObject->id,
                'claimer_id' => $claimer->id,
                'institution_targeted_id' => $unit->institution->id,
                'request_channel_slug' => \Satis2020\ServicePackage\Models\Channel::all()->random()->slug,
                'response_channel_slug' => \Satis2020\ServicePackage\Models\Channel::where('is_response', true)->get()->random()->slug,
                'event_occured_at' => $faker->date('Y-m-d H:i:s'),
                'amount_disputed' => $faker->numberBetween(50000, 1000000),
                'amount_currency_slug' => \Satis2020\ServicePackage\Models\Currency::all()->random()->slug,
                'is_revival' => $faker->randomElement([true, false]),
                'created_by' => $staffCollector->id,
                'status' => 'full',
                'reference' => date('Y') . date('m') . '-' . $faker->randomNumber(6, true),
                'claimer_expectation' => $faker->text
            ]);

            //register a treatment
            $activeTreatment = Treatment::create([
                'id' => (string)Str::uuid(),
                'claim_id' => $claim->id,
                'transferred_to_unit_at' => Carbon::now(),
                'responsible_unit_id' => $unit->id,
                'assigned_to_staff_at' => $claim->created_at->addDays($faker->numberBetween(1, 7)),
                'assigned_to_staff_by' => $staffCaterer->id,
                'responsible_staff_id' => $staffCaterer->id
            ]);

            //update claim
            $claim->update(['active_treatment_id' => $activeTreatment->id, 'status' => 'assigned_to_staff']);
        }

    }
}
