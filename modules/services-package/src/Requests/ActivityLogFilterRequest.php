<?php

namespace Satis2020\ServicePackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
/**
 * Class ActivityLogFilterRequest
 * @package Satis2020\ServicePackage\Requests
 */
class ActivityLogFilterRequest extends FormRequest
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
            'causer_id' => 'nullable|exists:users,id',
            'log_action' => 'nullable',
            'date_start' => 'sometimes|date_format:Y-m-d',
            'date_end' => 'sometimes|date_format:Y-m-d|after_or_equal:date_start'
        ];
    }

}