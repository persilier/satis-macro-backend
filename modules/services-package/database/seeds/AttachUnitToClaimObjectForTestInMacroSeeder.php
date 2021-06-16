<?php

namespace Satis2020\ServicePackage\Database\Seeds;

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
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Role;

class AttachUnitToClaimObjectForTestInMacroSeeder extends Seeder
{

    public function attachUnitToClaimObject($claimObject, $unit)
    {
        if (!DB::table('claim_object_unit')
            ->where('claim_object_id', $claimObject->id)
            ->where('unit_id', $unit->id)
            ->exists()) {
            $claimObject->units()->attach($unit->id, ['institution_id' => $unit->institution->id]);
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // claimObject = Relevé trimestriel non reçu && unit = Consulting
        $this->attachUnitToClaimObject(ClaimObject::findOrFail('4fbf51db-c09d-4d8a-a6a8-3d0e2e4abbc2'), Unit::findOrFail('3323b3c1-143e-4e66-a566-4f022f329a48'));

        // claimObject = Relevé mensuel non reçu && unit = Direction
        $this->attachUnitToClaimObject(ClaimObject::findOrFail('7068c74d-aac0-4e6a-801d-d974b76a0bd7'), Unit::findOrFail('0de6e0d3-44d1-4ebd-a74b-32e4b367ddb2'));

        // claimObject = Demande d'ouverture de compte && unit = Consulting
        $this->attachUnitToClaimObject(ClaimObject::findOrFail('802fcb6d-d0d1-4b4a-b0e1-aa24cc79f3d3'), Unit::findOrFail('3323b3c1-143e-4e66-a566-4f022f329a48'));

        // claimObject = Débit à tort GIM && unit = Direction
        $this->attachUnitToClaimObject(ClaimObject::findOrFail('b827f066-cdb7-424d-a587-0a80e54c86e3'), Unit::findOrFail('0de6e0d3-44d1-4ebd-a74b-32e4b367ddb2'));

    }
}
