<?php

namespace Satis2020\Escalation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\Escalation\Rules\StandardBoardExists;
use Satis2020\Escalation\Rules\ValidateClaimId;

class TreatmentBoardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $rules = [
            'name' => [
                'string',
                Rule::requiredIf($this->type == TreatmentBoard::SPECIFIC && strtoupper($this->getMethod()) === "POST"),
                Rule::unique('treatment_boards', 'name')->ignore($this->id)
            ],
            'claim_id' => [Rule::requiredIf($this->type == TreatmentBoard::SPECIFIC && strtoupper($this->getMethod()) === "POST"), 'exists:claims,id',],
            'type' => ['required', Rule::in([TreatmentBoard::STANDARD, TreatmentBoard::SPECIFIC]), new StandardBoardExists($this)],
            'description' => ['string', 'nullable'],
            'members' => ['array', 'required_if:type,' . TreatmentBoard::SPECIFIC],
            'members.*' => ['exists:staff,id'],
            'specific_bord_exists' => ['boolean'],
        ];


        return $rules;
    }
}
