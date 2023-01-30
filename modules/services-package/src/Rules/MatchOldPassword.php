<?php

namespace Satis2020\ServicePackage\Rules;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Satis2020\ServicePackage\Services\Auth\UpdatePasswordService;

/**
 * Class MatchOldPassword
 * @package Satis2020\ServicePackage\Rules
 */
class MatchOldPassword implements Rule
{
    protected $password;
    protected $email;
    /**
     * @var Application|mixed
     */
    private $updatePasswordService;

    public function __construct($password, $email)
    {
        $this->password = $password;
        $this->email = $email;
        $this->updatePasswordService = app(UpdatePasswordService::class);
        $this->getPasswordUser();
    }
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Hash::check($value, $this->password);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is match with old password.';
    }

    protected function getPasswordUser()
    {
        if (is_null($this->password)) {
            $this->password = $this->updatePasswordService->getByEmail($this->email)->password;
        }
    }
}
