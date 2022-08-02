<?php

namespace Satis2020\ServicePackage\Requests\Reporting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\RequiredIf;

/**
 * Class StateReportingRequest
 * @package Satis2020\ServicePackage\Requests
 */
class RegulatoryStateReportingRequest extends FormRequest
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
            'number_of_claims_litigated_in_court'=>["numeric","min:0"],
            'total_amount_of_claims_litigated_in_court'=>["numeric","min:0"],
            "institution_id"=>['sometimes','exists:institutions,id'],
            "unit_id"=>['sometimes','exists:units,id'],
        ];
    }

}