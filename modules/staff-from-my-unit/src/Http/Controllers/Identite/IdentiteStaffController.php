<?php

namespace Satis2020\StaffFromMyUnit\Http\Controllers\Identite;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class IdentiteStaffController extends ApiController
{
    use VerifyUnicity, Telephone;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:store-staff-from-any-unit')->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Identite $identite
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Identite $identite)
    {
        $rules = [
            'firstname' => 'required',
            'lastname' => 'required',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => ['required', 'array', new TelephoneArray],
            'email' => ['required', 'array', new EmailArray],
            'position_id' => 'required|exists:positions,id',
            'unit_id' => 'required|exists:units,id',
            'institution_id' => 'required|exists:institutions,id'
        ];

        $this->validate($request, $rules);

        $request->merge(['telephone' => $this->removeSpaces($request->telephone)]);

        // Institution & Unit Consistency Verification
        if (!$this->handleUnitInstitutionVerification($request->institution_id, $request->unit_id)) {
            return response()->json([
                'status' => false,
                'message' => 'The unit must be linked to the institution'
            ], 409);
        }

        // Staff PhoneNumber Unicity Verification
        $verifyPhone = $this->handleStaffIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', 'id', $identite->id);
        if (!$verifyPhone['status']) {
            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyPhone, 409);
        }

        // Staff Email Unicity Verification
        $verifyEmail = $this->handleStaffIdentityVerification($request->email, 'identites', 'email', 'email', 'id', $identite->id);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyEmail, 409);
        }

        $identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        $staff = Staff::create([
            'identite_id' => $identite->id,
            'position_id' => $request->position_id,
            'unit_id' => $request->unit_id,
            'institution_id' => $request->institution_id,
            'others' => $request->others
        ]);

        return response()->json($staff->load('identite', 'position', 'unit', 'institution'), 201);

    }
    
}
