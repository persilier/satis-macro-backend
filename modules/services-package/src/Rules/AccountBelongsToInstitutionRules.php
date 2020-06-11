<?php

namespace Satis2020\ServicePackage\Rules;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;

class AccountBelongsToInstitutionRules implements Rule
{

    protected $institution_id;

    public function __construct($institution_id)
    {
        $this->institution_id = $institution_id;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     * @throws CustomException
     */

    public function passes($attribute, $value)
    {
        $account = Account::with('client_institution.institution')->where('id', $value)->firstOrFail();
        try{
            $condition = $account->client_institution->institution->id === $this->institution_id;
        }catch (\Exception $exception){
            throw new CustomException("Can't retrieve the account institution");
        }
        return $condition;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The account must belong to the chosen institution';
    }

}
