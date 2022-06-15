<?php

namespace Satis2020\Escalation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return [
            'name' => ['string',
                Rule::requiredIf($this->isNotFilled('specific_bord_exists')),
                Rule::unique('treatment_boards','name')->ignore($this->id)],
            'description'=>['string','nullable'],
            'members' => ['array','required'],
            'members.*' => ['exists:staff,id'],
            'specific_bord_exists' => ['boolean'],
        ];
    }
}
