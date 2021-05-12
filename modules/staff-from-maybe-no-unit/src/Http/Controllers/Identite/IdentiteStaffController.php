<?php

namespace Satis2020\StaffFromMaybeNoUnit\Http\Controllers\Identite;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\StaffManagement;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class IdentiteStaffController extends ApiController
{
    use VerifyUnicity, Telephone, DataUserNature, StaffManagement, IdentityManagement;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-staff-from-maybe-no-unit')->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Identite $identite
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request, Identite $identite)
    {
        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rules(false));

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

        if ($request->has('unit_id')) {
            // Institution & Unit Consistency Verification
            $this->handleUnitInstitutionVerification($request->institution_id, $request->unit_id);
        }

        // Staff PhoneNumber and Email Unicity Verification
        $this->handleStaffPhoneNumberAndEmailVerificationUpdate($request, $identite);

        $identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        $this->updateIdentity($request, $identite);

        $staff = $request->has('unit_id')
            ? $this->createStaff($request, $identite)
            : $this->createStaff($request, $identite, false);

        return response()->json($staff->load('identite', 'position', 'unit', 'institution'), 201);

    }
    
}
