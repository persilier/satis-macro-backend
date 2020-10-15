<?php

namespace Satis2020\ActivePilot\Http\Controllers\ActivePilot;

use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\ActivePilot;
use Spatie\Permission\Models\Role;

/**
 * Class UnitTypeController
 * @package Satis2020\ActivePilot\Http\Controllers\UnitType
 */
class ActivePilotController extends ApiController
{

    use ActivePilot;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:update-active-pilot')->only(['edit', 'update']);
    }

    /**
     * Display the specified resource.
     *
     * @param Institution $institution
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function edit(Institution $institution)
    {
        $staff = $this->staff();

        $checkIfStaffIsPilot = $this->checkIfStaffIsPilot($staff);

        if (($checkIfStaffIsPilot && !$staff->is_active_pilot) || $staff->institution_id != $institution->id) {
            return response()->json('Unauthorized', 401);
        }

        $roleName = $this->getPilotRoleNameByInstitution($institution);

        if (is_null($roleName)) {
            return response()->json("Can not found any pilot", 404);
        }

        return response()->json(Staff::with('identite.user')
            ->where('institution_id', $institution->id)
            ->get()
            ->filter(function ($value, $key) use ($roleName, $staff, $checkIfStaffIsPilot) {
                if ($checkIfStaffIsPilot && $staff->is_active_pilot && $staff->id == $value->id) {
                    return false;
                }
                return $value->identite->user->hasRole($roleName);
            })
            ->values(), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Institution $institution
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function update(Request $request, Institution $institution)
    {

        $staff = $this->staff();

        if (($this->checkIfStaffIsPilot($staff) && !$staff->is_active_pilot) || $staff->institution_id != $institution->id) {
            return response()->json('Unauthorized', 401);
        }

        $rules = [
            'staff_id' => [
                'required',
                function ($attribute, $value, $fail) use ($institution) {
                    $staff = Staff::with(['institution.institutionType', 'identite.user'])->findOrFail($value);

                    if ($staff->institution_id != $institution->id) {
                        $fail($attribute . ' does not belong to the institution.');
                    }

                    if (!$this->checkIfStaffIsPilot($staff)) {
                        $fail($attribute . ' is not a pilot.');
                    }
                },
            ]
        ];

        $this->validate($request, $rules);

        $institution->update(['active_pilot_id' => $request->staff_id]);

        return response()->json($institution, 201);
    }

}
