<?php

namespace Satis2020\StaffFromMyUnit\Http\Controllers\Identite;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $this->middleware('permission:store-staff-from-my-unit')->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Identite $identite
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request, Identite $identite)
    {
        $request->merge(['institution_id' => $this->institution()->id]);

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rules());

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

        // Institution & Unit Consistency Verification
        $this->handleUnitInstitutionVerification($request->institution_id, $request->unit_id);

        // Staff PhoneNumber and Email Unicity Verification
        $this->handleStaffPhoneNumberAndEmailVerificationUpdate($request, $identite);

        $this->updateIdentity($request, $identite);

        $staff = $this->createStaff($request, $identite);

        return response()->json($staff->load('identite', 'position', 'unit', 'institution'), 201);

    }
    
}
