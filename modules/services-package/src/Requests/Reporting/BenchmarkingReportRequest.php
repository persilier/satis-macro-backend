<?php

namespace Satis2020\ServicePackage\Requests\Reporting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ActivityLogFilterRequest
 * @package Satis2020\ServicePackage\Requests
 */
class BenchmarkingReportRequest extends FormRequest
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
            'date_start' => ['date_format:Y-m-d','required'],
            'date_end' => ['date_format:Y-m-d','after_or_equal:date_start','required'],
            "institution_id"=>['sometimes','exists:institutions,id'],
        ];
    }

}