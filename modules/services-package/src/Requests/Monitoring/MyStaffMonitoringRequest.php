<?php
namespace Satis2020\ServicePackage\Requests\Monitoring;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
/**
* Class ActivityLogFilterRequest
* @package Satis2020\ServicePackage\Requests
*/
class MyStaffMonitoringRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            "staff_id"=>['required'],
            "institution_id"=>['sometimes','exists:institutions,id']
        ];
    }

}