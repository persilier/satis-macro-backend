<?php

namespace Satis2020\StaffFromAnyUnit\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\InstitutionPackage\Http\Resources\Institution as InstitutionResource;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Traits\Telephone;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class StaffController extends ApiController
{
    use VerifyUnicity, Telephone;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-staff-from-any-unit')->only(['index']);
        $this->middleware('permission:show-staff-from-any-unit')->only(['show']);
        $this->middleware('permission:store-staff-from-any-unit')->only(['store']);
        $this->middleware('permission:update-staff-from-any-unit')->only(['update']);
        $this->middleware('permission:destroy-staff-from-any-unit')->only(['destroy']);
        $this->middleware('permission:edit-staff-from-any-unit')->only(['edit']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Staff::with(['identite', 'position', 'unit', 'institution'])->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
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
        $verifyPhone = $this->handleStaffIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone');
        if (!$verifyPhone['status']) {
            return response()->json($verifyPhone, 409);
        }

        // Staff Email Unicity Verification
        $verifyEmail = $this->handleStaffIdentityVerification($request->email, 'identites', 'email', 'email');
        if (!$verifyEmail['status']) {
            return response()->json($verifyEmail, 409);
        }

        $identite = Identite::create($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        $staff = Staff::create([
            'identite_id' => $identite->id,
            'position_id' => $request->position_id,
            'unit_id' => $request->unit_id,
            'institution_id' => $request->institution_id,
            'others' => $request->others
        ]);

        return response()->json($staff->load('identite', 'position', 'unit', 'institution'), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function show(Staff $staff)
    {
        return response()->json($staff->load('identite', 'position', 'unit', 'institution'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function edit(Staff $staff)
    {
        $staff->load('identite', 'position', 'unit', 'institution.units');
        return response()->json([
            'staff' => $staff,
            'institutions' => Institution::all(),
            'positions' => Position::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Staff $staff)
    {
        $staff->load('identite', 'position', 'unit', 'institution');

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
        $verifyPhone = $this->handleStaffIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', 'id', $staff->identite->id);
        if (!$verifyPhone['status']) {
            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyPhone, 409);
        }

        // Staff Email Unicity Verification
        $verifyEmail = $this->handleStaffIdentityVerification($request->email, 'identites', 'email', 'email', 'id', $staff->identite->id);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
            return response()->json($verifyEmail, 409);
        }

        $staff->update([
            'position_id' => $request->position_id,
            'unit_id' => $request->unit_id,
            'institution_id' => $request->institution_id,
            'others' => $request->others
        ]);

        $staff->identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        return response()->json($staff, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Staff $staff)
    {
        $staff->delete();

        return response()->json($staff, 200);
    }

}
