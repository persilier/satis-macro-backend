<?php

namespace Satis2020\ServicePackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class AuthConfigRequest extends FormRequest
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
        $rules =  [
            'inactivity_control' => ['boolean'],
            'inactivity_time_limit' => ["numeric","min:1",Rule::requiredIf($this->inactivity_control==true)],
            'inactive_account_msg' => ["string",Rule::requiredIf($this->inactivity_control==true)],
            'password_expiration_control' => ['boolean'],
            'password_lifetime' => ["numeric","min:1",Rule::requiredIf($this->password_expiration_control==true)],
            'max_password_histories' => ["numeric","min:1",Rule::requiredIf($this->password_expiration_control==true)],
            'password_notif_delay' => ["numeric","min:1",Rule::requiredIf($this->password_expiration_control==true)],
            'password_notif_msg' => ["string",Rule::requiredIf($this->password_expiration_control==true)],
            'password_expiration_msg' => ["string",Rule::requiredIf($this->password_expiration_control==true)],
            'block_attempt_control' => ['boolean'],
            'max_attempt' => ["numeric","min:1",Rule::requiredIf($this->block_attempt_control==true)],
            'attempt_delay' => ["numeric","min:1",Rule::requiredIf($this->block_attempt_control==true)],
            'attempt_waiting_time' => ["numeric","min:1",Rule::requiredIf($this->block_attempt_control==true)],
            'account_blocked_msg' => ["string",Rule::requiredIf($this->block_attempt_control==true)],
        ];

        if ($this->getMethod()=="PUT"){
            $rules["id"] = ['required','exists:metadata,id'];
        }

        return  $rules;
    }
}
