<?php

namespace Satis2020\Escalation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Satis2020\Escalation\Rules\StandardBoardExists;

class EscalationConfigRequest extends FormRequest
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

        if ($this->isNotFilled('specific_bord_exists')){
            $this->merge(["specific_bord_exists"=>0]);
        }
        $rules =  [
            'standard_bord_exists' => ['boolean',Rule::requiredIf($this->isNotFilled('specific_bord_exists')),new StandardBoardExists($this)],
            'specific_bord_exists' => ['boolean'],
            'name' => ["string","min:1",Rule::requiredIf($this->standard_bord_exists)],
            'members' => ['array',Rule::requiredIf($this->standard_bord_exists)],
            'members.*' => ['exists:staff,id'],
        ];

        if ($this->getMethod()=="PUT"){
            $rules["id"] = ['required','exists:metadata,id'];
        }

        return  $rules;
    }
}
