<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Repositories\UserRepository;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

/**
 * Trait StaffManagement
 * @package Satis2020\ServicePackage\Traits
 */
trait StaffManagement
{
    /**
     * @param bool $required_unit
     * @return array
     */
    protected function rules($required_unit = true,$identite_id=null)
    {

        $data = [
            'firstname' => 'required',
            'lastname' => 'required',
            'sexe' => ['required'],
            'telephone' => ['required', 'array', new TelephoneArray],
            'email' => ['required', 'array', new EmailArray],
            'position_id' => 'required|exists:positions,id',
            'institution_id' => 'required|exists:institutions,id',
            'is_lead' => [Rule::in([true, false])]
        ];

        if ($required_unit) {
            $data['unit_id'] = 'required|exists:units,id';
        } else {
            $data['unit_id'] = 'exists:units,id';
        }

        if($identite_id!=null){
            $userRepositories = app(UserRepository::class);
            $data['email'] =  ['required', Rule::unique('users','username')->ignore($userRepositories->getUserByIdentity($identite_id)),'array', new EmailArray];
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
            'others' => $request->others
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
        $activityLogService->store("Création d'un staff.",
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

            if($request->is_lead){

                $unit->update(['lead_id' => $staff->id]);

            }else{

                if($unit->lead_id == $staff->id){

                    $unit->update(['lead_id' => NULL]);
                }
            }
            

        }

        if($request->has('unit_id')){

            $unitStaff = $staff->unit;

            if($request->unit_id != $unitStaff->id && $unitStaff->lead_id == $staff->id){


                $unitStaff->update(['lead_id' => NULL]);

            }

        }

        $activityLogService = app(ActivityLogService::class);
        $activityLogService->store("Mise à jour d'un staff",
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
}
