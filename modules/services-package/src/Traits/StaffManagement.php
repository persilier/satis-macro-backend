<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;

trait StaffManagement
{
    /**
     * @param bool $required_unit
     * @return array
     */
    protected function rules($required_unit = true)
    {
        $data = [
            'firstname' => 'required',
            'lastname' => 'required',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => ['required', 'array', new TelephoneArray],
            'email' => ['required', 'array', new EmailArray],
            'position_id' => 'required|exists:positions,id',
            'institution_id' => 'required|exists:institutions,id'
        ];

        if ($required_unit) {
            $data['unit_id'] = 'required|exists:units,id';
        } else {
            $data['unit_id'] = 'exists:units,id';
        }

        return $data;

    }

    /**
     * @param $request
     * @param $identite
     * @param bool $with_unit
     * @return array
     */
    protected function createStaff($request, $identite, $with_unit = true)
    {
        $data = [
            'identite_id' => $identite->id,
            'position_id' => $request->position_id,
            'institution_id' => $request->institution_id,
            'others' => $request->others
        ];

        if ($with_unit) {
            $data['unit_id'] = $request->unit_id;
        }

        return $staff = Staff::create($data);
    }

    protected function updateStaff($request, $staff, $with_unit = true)
    {
        $data = [
            'position_id' => $request->position_id,
            'institution_id' => $request->institution_id,
            'others' => $request->others
        ];

        if ($with_unit) {
            $data['unit_id'] = $request->unit_id;
        }

        return $staff->update($data);
    }

    /**
     * @param $staff
     * @throws CustomException
     */
    protected function checkIfStaffBelongsToMyInstitution($staff, $institution_id)
    {
        $staff->load('identite', 'position', 'unit', 'institution');

        try{
            $condition = $staff->institution->id !== $institution_id;
        }catch (\Exception $exception){
            throw new CustomException("Can't retrieve the staff institution");
        }

        if ($condition){
            throw new CustomException("You do not own this staff.");
        }

    }
}