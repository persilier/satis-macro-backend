<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Role;

class ResetProcessSeeder extends Seeder
{


    public function truncateProcessTables()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Treatment::truncate();
        Claim::truncate();
        File::truncate();
        Unit::truncate();
        Client::truncate();
        DB::table('client_institution')->truncate();
        DB::table('claim_object_unit')->truncate();
        Account::truncate();
        Identite::withTrashed()->with(['staff', 'user'])->get()->map(function ($item, $key) {
            if (is_null($item->user)) {
                if (!is_null($item->staff)) {
                    $item->staff->forceDelete();
                }
                $item->forceDelete();
            } elseif (!in_array($item->user->id, [
                '6f53d239-2890-4faf-9af9-f5a97aee881e',
                'ceefcca8-35c6-4e62-9809-42bf6b9adb20',
                '94656cd3-d0c7-45bb-83b6-5ded02ded07b',
                '18732c5e-b485-474e-811d-de9bbb8d6cf2',
                '8df01ee3-7f66-4328-9510-f75666f4bc4a'
            ])) {

                if (!is_null($item->staff)) {
                    $item->staff->forceDelete();
                }

                if (!is_null($item->user)) {
                    $item->user->forceDelete();
                }

                $item->forceDelete();
            }
            return true;
        });
        Staff::withTrashed()->with(['identite.user'])->get()->map(function ($item, $key) {
            if (is_null($item->identite)) {
                $item->forceDelete();
            } elseif (is_null($item->identite->user)) {
                $item->identite->forceDelete();
                $item->forceDelete();
            } elseif (!in_array($item->identite->user->id, [
                '6f53d239-2890-4faf-9af9-f5a97aee881e',
                'ceefcca8-35c6-4e62-9809-42bf6b9adb20',
                '94656cd3-d0c7-45bb-83b6-5ded02ded07b',
                '18732c5e-b485-474e-811d-de9bbb8d6cf2',
                '8df01ee3-7f66-4328-9510-f75666f4bc4a'
            ])) {
                $item->identite->user->forceDelete();
                $item->identite->forceDelete();
                $item->forceDelete();
            }
            return true;
        });
        User::withTrashed()->get()->map(function ($item, $key) {
            if (!in_array($item->id, [
                '6f53d239-2890-4faf-9af9-f5a97aee881e',
                'ceefcca8-35c6-4e62-9809-42bf6b9adb20',
                '94656cd3-d0c7-45bb-83b6-5ded02ded07b',
                '18732c5e-b485-474e-811d-de9bbb8d6cf2',
                '8df01ee3-7f66-4328-9510-f75666f4bc4a'
            ])) {
                if (is_null($item->identite)) {
                    $item->forceDelete();
                } elseif (is_null($item->identite->staff)) {
                    $item->identite->forceDelete();
                    $item->forceDelete();
                } else {
                    $item->identite->staff->forceDelete();
                    $item->identite->forceDelete();
                    $item->forceDelete();
                }
            }
            return true;
        });
        DB::table('model_has_roles')->where('role_id', '!=', 1)->delete();
    }

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

    public function createUser($identity, $roleName)
    {
        $user = User::create([
            'id' => (string)Str::uuid(),
            'username' => $identity->email[0],
            'password' => bcrypt('123456789'),
            'identite_id' => $identity->id,
            'disabled_at' => null
        ]);

        $user->assignRole(
            Role::where('name', $roleName)->where('guard_name', 'api')->firstOrFail()
        );

        return $user;
    }

    public function createStaff($unit, $roleName)
    {
        $identity = $this->createIdentity();

        $user = $this->createUser($identity, $roleName);

        return $staff = Staff::create([
            'id' => (string)Str::uuid(),
            'identite_id' => $identity->id,
            'position_id' => \Satis2020\ServicePackage\Models\Position::all()->random()->id,
            'institution_id' => is_null($unit->institution) ? Institution::all()->random()->id : $unit->institution->id,
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

    public function createUnits()
    {
        $faker = Faker::create();

        $institutions = Institution::with('institutionType')->get();

        foreach ($institutions as $institution) {

            $nature = env('APP_NATURE');

            $roleName = 'pilot';
            if ($nature === 'MACRO') {
                if ($institution->institutionType->name == 'holding') {
                    $roleName = 'pilot-holding';
                } elseif ($institution->institutionType->name == 'filiale') {
                    $roleName = 'pilot-filial';
                }
            }

            $unit = Unit::create([
                'id' => (string)Str::uuid(),
                'name' => $faker->word,
                'description' => $faker->text,
                'unit_type_id' => UnitType::all()->random()->id,
                'parent_id' => null,
                'institution_id' => ($institution->institutionType->name === 'observatory' || $institution->institutionType->name === 'membre') ? null : $institution->id
            ]);

            // search if a user in that institution has the pilot role
            if (User::with('identite.staff')->get()->search(function ($value, $key) use ($institution, $roleName) {
                    if (is_null($value->identite)) {
                        return false;
                    }
                    if (is_null($value->identite->staff)) {
                        return false;
                    }
                    return $value->identite->staff->institution_id == $institution->id && $value->hasRole($roleName);
                }) === false) {
                $this->createStaff($unit, $roleName);
            }

        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->truncateProcessTables();

        $this->createUnits();

        $faker = Faker::create();

        $units = Unit::with('institution.institutionType')->get();

        foreach ($units as $unit) {
            // register the staff collector
            if (!is_null($unit->institution)) {
                $staffCollector = $unit->institution->institutionType->name === 'holding'
                    ? $this->createStaff($unit, 'collector-holding')
                    : $this->createStaff($unit, 'collector-filial-pro');
            } else {
                $staffCollector = $this->createStaff($unit, 'collector-observatory');
            }

            // register the staff lead
            $staffLead = $this->createStaff($unit, 'staff');
            $unit->update(['lead_id' => $staffLead->id]);

            // register a simple staff
            $staff = $this->createStaff($unit, 'staff');

            $numberClaims = $faker->numberBetween(5, 10);

            for ($i = 1; $i <= $numberClaims; $i++) {
                // register the claim
                $claimer = $this->createIdentity();
                $claimObject = \Satis2020\ServicePackage\Models\ClaimObject::all()->random();
                $this->attachUnitToClaimObject($claimObject, $unit);
                $claim = Claim::create([
                    'id' => (string)Str::uuid(),
                    'description' => $faker->text,
                    'claim_object_id' => $claimObject->id,
                    'claimer_id' => $claimer->id,
                    'institution_targeted_id' => is_null($unit->institution) ? Institution::all()->random()->id : $unit->institution->id,
                    'request_channel_slug' => \Satis2020\ServicePackage\Models\Channel::all()->random()->slug,
                    'response_channel_slug' => \Satis2020\ServicePackage\Models\Channel::where('is_response', true)->get()->random()->slug,
                    'event_occured_at' => $faker->date('Y-m-d H:i:s'),
                    'amount_disputed' => $faker->numberBetween(50000, 1000000),
                    'amount_currency_slug' => \Satis2020\ServicePackage\Models\Currency::all()->random()->slug,
                    'is_revival' => $faker->randomElement([true, false]),
                    'created_by' => $staffCollector->id,
                    'status' => 'full',
                    'reference' => date('Y') . date('m') . '-' . $faker->randomNumber(6, true),
                    'claimer_expectation' => $faker->text,
                    'completed_by' => $staffCollector->id,
                    'completed_at' => Carbon::now()
                ]);

            }

        }

    }
}
