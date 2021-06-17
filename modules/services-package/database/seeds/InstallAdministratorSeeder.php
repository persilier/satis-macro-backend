<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\requirement;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Role;

class InstallAdministratorSeeder extends Seeder
{

    public function createUnitType($unitTypeName)
    {
        return $unitType = UnitType::create([
            'name' => $unitTypeName,
            'can_be_target' => false,
            'can_treat' => true,
            'is_editable' => false
        ]);
    }

    public function createUnit($unitName, $unitTypeId, $institutionId = NULL)
    {
        return $unit = Unit::create([
            'name' => $unitName,
            'unit_type_id' => $unitTypeId,
            'institution_id' => $institutionId
        ]);
    }

    public function createPosition($positionName)
    {
        return $position = Position::create([
            'name' => $positionName
        ]);
    }

    public function createIdentity($firstName, $lastName, $gender, $phoneNumber, $email)
    {
        return $identity = Identite::create([
            'id' => (string)Str::uuid(),
            'firstname' => $firstName,
            'lastname' => $lastName,
            'sexe' => $gender,
            'telephone' => [$phoneNumber],
            'email' => [$email]
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

        $role = Role::where('name', $roleName)->where('guard_name', 'api')->firstOrFail();

        $user->assignRole($role);

        return $user;
    }

    public function createStaff($institution, $unit, $roleName, $firstName, $lastName, $gender, $phoneNumber, $email, $position, $isLead = false)
    {
        $identity = $this->createIdentity($firstName, $lastName, $gender, $phoneNumber, $email);

        $user = $this->createUser($identity, $roleName);

        $staff = Staff::create([
            'id' => (string)Str::uuid(),
            'identite_id' => $identity->id,
            'position_id' => $position->id,
            'institution_id' => $institution->id,
            'unit_id' => $unit->id,
            'feedback_preferred_channels' => ['email']
        ]);

        if ($isLead) {
            $unit->update(['lead_id' => $staff->id]);
        }

        return $staff;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $appNature = Config::get('services.app_nature', 'PRO');

        // create unit type
        $unitType = $this->createUnitType('SERVICE');

        $institution = Institution::firstOrFail();

        // create unit
        if ($appNature == 'HUB') {
            $unit = $this->createUnit('ADMINISTRATION', $unitType->id);
        } else {
            $unit = $this->createUnit('ADMINISTRATION', $unitType->id, $institution->id);
        }

        // create position
        $position = $this->createPosition('ADMINISTRATEUR PLATEFORME');

        $roleName = 'admin-pro';

        if ($appNature == 'MACRO')
            $roleName = 'admin-holding';

        if ($appNature == 'HUB')
            $roleName = 'admin-observatory';

        // create staff
        $staff = $this->createStaff($institution, $unit, $roleName, 'ADMIN', 'Admin', 'M',
            '63656565', 'contact@dmdsatis.com', $position, true);

    }
}
