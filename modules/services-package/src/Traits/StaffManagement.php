<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Repositories\UserRepository;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

/**
 * Trait StaffManagement
 * @package Satis2020\ServicePackage\Traits
 */
trait StaffManagement
{
    use DataUserNature;
    /**
     * @param bool $required_unit
     * @return array
     */
    protected function rules($required_unit = true, $identite_id = null)
    {

        $data = [
            'firstname' => 'required',
            'lastname' => 'required',
            'sexe' => ['required'],
            'telephone' => ['required', 'array', new TelephoneArray],
            'email' => ['required', 'array', new EmailArray],
            'email.*' => ['email'],
            'position_id' => 'required|exists:positions,id',
            'institution_id' => 'required|exists:institutions,id',
            'is_lead' => [Rule::in([true, false])]
        ];

        if ($required_unit) {
            $data['unit_id'] = 'required|exists:units,id';
        } else {
            $data['unit_id'] = 'exists:units,id';
        }

        if ($identite_id != null) {
            $userRepositories = app(UserRepository::class);
            $data['email'] =  ['required', Rule::unique('users', 'username')->ignore($userRepositories->getUserByIdentity($identite_id)), 'array', new EmailArray];
        }
        return $data;
    }

    /**
     * @param $request
     * @param $identite
     * @return array
     */
    protected function createStaff($request, $identite)
    {
        $data = [
            'identite_id' => $identite->id,
            'position_id' => $request->position_id,
            'institution_id' => $request->institution_id,
            'others' => $request->others,
            'feedback_preferred_channels' => ["email"]
        ];

        if ($request->has('unit_id')) {
            $data['unit_id'] = $request->unit_id;
        }

        $staff = Staff::create($data);

        if ($request->has('unit_id') && $request->has('is_lead') && $request->is_lead) {

            $unit = Unit::find($request->unit_id);

            $unit->update(['lead_id' => $staff->id]);
        }

        $activityLogService = app(ActivityLogService::class);
        $activityLogService->store(
            "Création d'un staff.",
            $this->institution()->id,
            ActivityLogService::STAFF_CREATED,
            'staff',
            $this->user(),
            $staff
        );

        return $staff;
    }

    protected function updateStaff($request, $staff)
    {
        $data = [
            'position_id' => $request->position_id,
            'institution_id' => $request->institution_id,
            'others' => $request->others
        ];

        if ($request->has('unit_id')) {
            $data['unit_id'] = $request->unit_id;
        }

        if ($request->has('unit_id') && $request->has('is_lead')) {

            $unit = Unit::find($request->unit_id);

            if ($request->is_lead) {

                $unit->update(['lead_id' => $staff->id]);
            } else {

                if ($unit->lead_id == $staff->id) {

                    $unit->update(['lead_id' => NULL]);
                }
            }
        }

        if ($request->has('unit_id')) {

            $unitStaff = $staff->unit;

            if ($request->unit_id != $unitStaff->id && $unitStaff->lead_id == $staff->id) {


                $unitStaff->update(['lead_id' => NULL]);
            }
        }

        $activityLogService = app(ActivityLogService::class);
        $activityLogService->store(
            "Mise à jour d'un staff",
            $this->institution()->id,
            ActivityLogService::UPDATE_STAFF,
            'staff',
            $this->user(),
            $staff
        );

        return $staff->update($data);
    }

    /**
     * @param $staff
     * @param $institution_id
     */
    protected function checkIfStaffBelongsToMyInstitution($staff, $institution_id)
    {
        $staff->load('identite', 'position', 'unit', 'institution');

        try {
            $condition = $staff->institution->id !== $institution_id;
        } catch (\Exception $exception) {
            throw new CustomException("Can't retrieve the staff institution");
        }

        if ($condition) {
            throw new CustomException("You do not own this staff.");
        }
    }

    public function getAllCollectors($institutionId = null)
    {
        $collector_role = Role::with('users')->where('name', 'collector-filial-pro')->first();
        if ($institutionId) {
            $collector =  $collector_role->users()
                ->whereHas('identite', function ($query) use ($institutionId) {
                    $query->whereHas('staff', function ($q) use ($institutionId) {
                        $q->where('institution_id', $institutionId);
                    });
                })->get()->load('identite.staff');
        } else {
            $collector =  $collector_role->users()->get()->load('identite.staff');
        }

        return $collector;
    }

    public function getRegisteredClaims($institutionId = null)
    {
        return Staff::when(!is_null($institutionId), function ($q) use ($institutionId) {
            $q->where('institution_id', $institutionId);
        })->with("registeredClaims", "identite")
            ->get()->filter(function ($value) {
                return sizeof($value->registeredClaims) > 0;
            })->values();
    }
    public function checkIfStaffHasUserAccount($staff)
    {
        $userRepo = app(UserRepository::class);
        return $userRepo->getUserByIdentity($staff->identite_id) != null;
    }

    protected function getAllStaff()
    {
        return Staff::with('identite.user')
            ->get()
            ->filter(function ($value, $key) {
                if (is_null($value->identite)) {
                    return false;
                }
                if (is_null($value->identite->user)) {
                    return false;
                }
                if ($value->identite != null && $value->identite->user != null && $value->identite->user->disabled_at != null) {
                    return false;
                }

                return $value->identite->user->hasRole('staff');
            })
            ->values();
    }
}
