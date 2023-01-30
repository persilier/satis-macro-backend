<?php

namespace Satis2020\ServicePackage\Requests\Reporting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ActivityLogFilterRequest
 * @package Satis2020\ServicePackage\Requests
 */
class DashboardRequest extends FormRequest
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
            'institution_targeted_id' => 'nullable|exists:institutions,id',
            'type' => 'required|in:day,period,month,30days,45days,3months',
            'day' => [Rule::requiredIf($this->type == 'day'), 'date_format:Y-m-d'],
            'date_start' => [Rule::requiredIf($this->type == 'period'), 'date_format:Y-m-d'],
            'date_end' => [Rule::requiredIf($this->type == 'period'), 'date_format:Y-m-d','after_or_equal:date_start'],
        ];
    }

}