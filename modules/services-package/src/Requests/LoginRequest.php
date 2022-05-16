<?php

namespace Satis2020\ServicePackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Rules\IsValidPasswordRules;
use Satis2020\ServicePackage\Rules\MatchOldPassword;
use Satis2020\ServicePackage\Repositories\UserRepository;
/**
 * Class UpdatePasswordRequest
 * @package Satis2020\ServicePackage\Requests
 */
class LoginRequest extends FormRequest
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
            'username' => ['required','email', 'exists:users,username'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if (auth()->user())
            $this->merge(['password_exist' => auth()->user()->password]);
        else
            $this->merge(['password_exist' => null]);
    }


}