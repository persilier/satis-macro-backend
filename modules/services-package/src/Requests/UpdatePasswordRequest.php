<?php

namespace Satis2020\ServicePackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Rules\IsValidPasswordRules;
use Satis2020\ServicePackage\Rules\MatchOldPassword;
use Satis2020\ServicePackage\Repositories\UserRepository;
/**
 * Class UpdatePasswordRequest
 * @package Satis2020\ServicePackage\Requests
 */
class UpdatePasswordRequest extends FormRequest
{
    /**
     * @var $userPassword
     */
    protected $userPassword;

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
            'email' => [Rule::requiredIf(is_null(auth()->user())), 'email', 'exists:users,username'],
            'current_password' => ['required', new MatchOldPassword($this->userPassword)],
            'new_password' => ['required', 'different:current_password', 'confirmed', new IsValidPasswordRules],
            'new_password_confirmation' => 'required'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $userRepository = app(UserRepository::class);

        if (auth()->user())
            $this->userPassword = auth()->user()->password;
        else
            $this->userPassword = $userRepository->getByEmail($this->email)->password;
    }


}