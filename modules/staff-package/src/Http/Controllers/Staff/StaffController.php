<?php

namespace Satis2020\StaffPackage\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\InstitutionPackage\Http\Resources\Institution as InstitutionResource;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class StaffController extends ApiController
{
    use VerifyUnicity;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Staff::with(['identite', 'position', 'unit'])->get(), 200);
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
            'telephone' => 'required|array',
            'email' => ['required', 'array', new EmailArray],
            'position_id' => 'required|exists:positions,id',
            'unit_id' => 'required|exists:units,id',
        ];

        $this->validate($request, $rules);

        // Position & Unit Consistency Verification
        if (!$this->handleSameInstitutionVerification($request->position_id, $request->unit_id)) {
            return response()->json([
                'status' => false,
                'message' => 'The unit and the position selected must be linked to the same institution'
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
            'others' => $request->others
        ]);

        return response()->json($staff->load('identite', 'position', 'unit'), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function show(Staff $staff)
    {
        return response()->json($staff->load('identite', 'position', 'unit.institution'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Satis2020\ServicePackage\Models\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function edit(Staff $staff)
    {
        $staff->load('identite', 'position', 'unit.institution');
        $staff->unit->institution->load(['positions', 'units'])->only(['positions', 'units']);
        return response()->json([
            'staff' => $staff,
            'institutions' => Institution::all()
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
        $staff->load('identite', 'position', 'unit.institution');
        $rules = [
            'firstname' => 'required',
            'lastname' => 'required',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => 'required|array',
            'email' => ['required', 'array', new EmailArray],
            'position_id' => 'required|exists:positions,id',
            'unit_id' => 'required|exists:units,id',
        ];

        $this->validate($request, $rules);

        // Position & Unit Consistency Verification
        if (!$this->handleSameInstitutionVerification($request->position_id, $request->unit_id)) {
            return response()->json([
                'status' => false,
                'message' => 'The unit and the position selected must be linked to the same institution'
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
