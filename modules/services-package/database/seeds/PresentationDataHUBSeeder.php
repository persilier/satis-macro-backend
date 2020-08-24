<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionMessageApi;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Relationship;
use Satis2020\ServicePackage\Models\ReportingTask;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Role;

class PresentationDataHUBSeeder extends Seeder
{

    public function truncateSomeTables()
    {
        Account::truncate();
        Claim::truncate();
        DB::table('claim_object_unit')->truncate();
        Client::truncate();
        DB::table('client_institution')->truncate();
        Discussion::truncate();
        DB::table('discussion_staff')->truncate();
        File::truncate();
        Identite::truncate();
        Institution::truncate();
        InstitutionMessageApi::truncate();
        DB::table('institution_position')->truncate();
        Message::truncate();
        Position::truncate();
        ReportingTask::truncate();
        DB::table('reporting_task_staff')->truncate();
        Staff::truncate();
        Treatment::truncate();
        Unit::truncate();
        UnitType::truncate();
        User::truncate();
        Relationship::truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('notifications')->truncate();
    }

    public function createInstitution($institutionName, $acronyme, $institutionTypeName)
    {
        return $institution = Institution::create([
            'name' => $institutionName,
            'acronyme' => $acronyme,
            'iso_code' => '229',
            'institution_type_id' => InstitutionType::where('name', $institutionTypeName)->firstOrFail()->id
        ]);
    }

    public function createUnitType($unitTypeName)
    {
        return $unitType =  UnitType::create([
            'name' => $unitTypeName,
            'can_be_target' => true,
            'can_treat' => true,
            'is_editable' => false
        ]);
    }

    public function createUnit($unitName, $unitTypeId, $institutionId)
    {
        return $unit =  Unit::create([
            'name' => $unitName,
            'unit_type_id' => $unitTypeId,
            'institution_id' => NULL
        ]);
    }

    public function createPosition($positionName)
    {
        return $position =  Position::create([
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

        $user->assignRole(
            Role::where('name', $roleName)->where('guard_name', 'api')->firstOrFail()
        );

        return $user;
    }

    public function createStaff($institutionId, $unit, $roleName, $firstName, $lastName, $gender, $phoneNumber, $email, $position, $isLead = false)
    {
        $identity = $this->createIdentity($firstName, $lastName, $gender, $phoneNumber, $email);

        $user = $this->createUser($identity, $roleName);

        $staff = Staff::create([
            'id' => (string)Str::uuid(),
            'identite_id' => $identity->id,
            'position_id' => $position->id,
            'institution_id' => $institutionId,
            'unit_id' => $unit->id
        ]);

        if ($isLead) {
            $unit->update(['lead_id' => $staff->id]);
        }

        return $staff;
    }

    public function createClient($firstName, $lastName, $gender, $phoneNumber, $email, $categoryClientId, $institutionId, $accountTypeId)
    {
        $identity = $this->createIdentity($firstName, $lastName, $gender, $phoneNumber, $email);

        $client = Client::create([
            'identites_id' => $identity->id
        ]);

        $categoryClient = CategoryClient::findOrFail($categoryClientId);
        $institution = Institution::findOrFail($institutionId);
        $accountType = AccountType::findOrFail($accountTypeId);

        $clientInstitution = $clientInstitution = ClientInstitution::create([
            'category_client_id' => $categoryClientId,
            'client_id' => $client->id,
            'institution_id' => $institutionId
        ]);

        $faker = Faker::create();

        $account = Account::create([
            'client_institution_id' => $clientInstitution->id,
            'account_type_id' => $accountTypeId,
            'number' => $faker->bankAccountNumber
        ]);

        return $client;
    }

    public function createRelationship($relationshipName)
    {
        return $relationship = Relationship::create([
            'name' => $relationshipName,
        ]);
    }

    public function attachUnitToClaimObject($claimObject, $unit)
    {
        if (!DB::table('claim_object_unit')
            ->where('claim_object_id', $claimObject->id)
            ->where('unit_id', $unit->id)
            ->exists()) {
            $claimObject->units()->attach($unit->id, ['institution_id' => NULL]);
        }
    }

    public function attachUnitToAllClaimObject($unit)
    {
        ClaimObject::all()->map(function ($item, $key) use ($unit) {
            $this->attachUnitToClaimObject($item, $unit);
            return $item;
        });
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nature = env('APP_NATURE');
        if ($nature === 'HUB') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            $this->truncateSomeTables();

            // create institutions
            $dmd = $this->createInstitution('DMD', 'dmd', 'observatory');
            $satis = $this->createInstitution('SATIS', 'satis', 'membre');

            // create unit types
            $department = $this->createUnitType('DEPARTEMENT');
            $service = $this->createUnitType('SERVICE');
            $direction = $this->createUnitType('DIRECTION');

            // create units
            $departmentProduction = $this->createUnit('PRODUCTION', $department->id, $dmd->id);
            $departmentExploitation = $this->createUnit('EXPLOITATION', $department->id, $dmd->id);
            $directionDmd = $this->createUnit('DIRECTION DMD', $direction->id, $dmd->id);
            $serviceInformatiqueDmd = $this->createUnit('INFORMATIQUE DMD', $service->id, $dmd->id);
            $serviceComptabiliteDmd = $this->createUnit('COMPTABILITE DMD', $service->id, $dmd->id);
            $serviceConsultingDmd = $this->createUnit('CONSULTING', $service->id, $dmd->id);
            $serviceAdministrationPlateformeDmd = $this->createUnit('ADMINISTRATION PLATEFORME DMD', $service->id, $dmd->id);

            $directionSatis = $this->createUnit('DIRECTION SATIS', $direction->id, $satis->id);
            $departmentCommercial = $this->createUnit('COMMERCIAL', $department->id, $satis->id);
            $serviceAdministrationPlateformeSatis = $this->createUnit('ADMINISTRATION PLATEFORME SATIS', $service->id, $satis->id);

            //create positions
            $directeur = $this->createPosition('DIRECTEUR');
            $developpeur = $this->createPosition('DEVELOPPEUR');
            $analyste = $this->createPosition('ANALYSTE');
            $directeurAdjoint = $this->createPosition('DIRECTEUR ADJOINT(E)');
            $assistantDirection = $this->createPosition('ASSISTANT(E) DE DIRECTION');
            $informaticien = $this->createPosition('INFORMATICIEN');
            $comptable = $this->createPosition('COMPTABLE');
            $administrateurPlateforme = $this->createPosition('ADMINISTRATEUR PLATEFORME');
            $pilotePlateforme = $this->createPosition('PILOTE PLATEFORME');
            $collecteurPlateforme = $this->createPosition('COLLECTEUR PLATEFORME');

            // create staff
            $nelson = $this->createStaff($dmd->id, $departmentProduction, 'staff', 'Nelson', 'AZONHOU', 'M',
                '67275158', 'ulrich@dmdconsult.com', $developpeur, true);

            $christian = $this->createStaff($dmd->id, $departmentProduction, 'staff', 'Guy Maurel Christian', 'AWASSI', 'M',
                '95952727', 'christian@dmdconsult.com', $developpeur);

            $onesin = $this->createStaff($dmd->id, $departmentProduction, 'staff', 'Onesine', 'LEWHE', 'M',
                '66766155', 'onesine@dmdconsult.com', $developpeur);

            $estelle = $this->createStaff($dmd->id, $departmentProduction, 'staff', 'Estelle', 'ODJO', 'F',
                '66844833', 'estelle@dmdconsult.com', $developpeur);

            $denis = $this->createStaff($dmd->id, $departmentExploitation, 'staff', 'Denis', 'GNARGO', 'M',
                '786833134', 'denis@dmdconsult.com', $developpeur, true);

            $michael = $this->createStaff($dmd->id, $departmentExploitation, 'staff', 'Michael', 'HOUANYE', 'M',
                '96370461', 'michael@dmdconsult.com', $analyste);

            $patrick = $this->createStaff($dmd->id, $directionDmd, 'staff', 'Patrick', 'DJONDO', 'M',
                '97124946', 'patrick@dmdconsult.com', $directeur, true);

            $carine = $this->createStaff($dmd->id, $directionDmd, 'staff', 'Carine', 'AKPLOGAN', 'F',
                '97327593', 'carine@dmdconsult.com', $directeurAdjoint);

            $edgar = $this->createStaff($dmd->id, $directionDmd, 'staff', 'Edgar', 'AYENA', 'M',
                '96055506', 'edgar@dmdconsult.com', $developpeur);

            $immaculee = $this->createStaff($dmd->id, $directionDmd, 'staff', 'Immaculée', "d'ALMEIDA", 'F',
                '97779555', 'contact@dmdconsult.com', $assistantDirection);

            $morel = $this->createStaff($dmd->id, $serviceInformatiqueDmd, 'staff', 'Morel', "DECADJEVI", 'M',
                '66259003', 'morel@dmdconsult.com', $informaticien, true);

            $nabil = $this->createStaff($dmd->id, $serviceInformatiqueDmd, 'staff', 'Nabil', "JOACHIM", 'M',
                '70555555', 'nabiljoachim@gmail.com', $informaticien);

            $armelle = $this->createStaff($dmd->id, $serviceComptabiliteDmd, 'staff', 'Armelle', "A RENSEIGNER", 'F',
                '70555570', 'finance@dmdconsult.com', $comptable, true);

            $merrith = $this->createStaff($dmd->id, $serviceConsultingDmd, 'staff', 'Merrith', "BOKONON", 'M',
                '97949497', 'merrith@dmdconsult.com', $analyste, true);

            $adminHolding = $this->createStaff($dmd->id, $serviceAdministrationPlateformeDmd, 'admin-observatory', 'ADMINISTRATEUR', "DMD", 'M',
                '70555571', 'admindmd@dmdconsult.com', $administrateurPlateforme, true);

            $collecteurHolding = $this->createStaff($dmd->id, $serviceAdministrationPlateformeDmd, 'collector-observatory', 'COLLECTEUR', "DMD", 'M',
                '70555572', 'collecteurdmd@dmdconsult.com', $collecteurPlateforme);

            $piloteHolding = $this->createStaff($dmd->id, $serviceAdministrationPlateformeDmd, 'pilot', 'PILOTE', "DMD", 'M',
                '70555573', 'pilotdmd@dmdconsult.com', $pilotePlateforme);




            $gildas = $this->createStaff($satis->id, $directionSatis, 'staff', 'Gildas', 'A RENSEIGNER', 'M',
                '70555574', 'gildas@dmdsatis.com', $directeur, true);

            $yessidatou = $this->createStaff($satis->id, $departmentCommercial, 'staff', 'Yessidatou', "A RENSEIGNER", 'F',
                '70555575', 'yessidatou@dmdsatis.com', $analyste, true);


            $relationshipClient = $this->createRelationship('CLIENT');
            $relationshipProspect = $this->createRelationship('PROSPECT');

            Unit::all()->map(function ($item, $key) {
                $this->attachUnitToAllClaimObject($item);
                return $item;
            });


        }
    }
}
