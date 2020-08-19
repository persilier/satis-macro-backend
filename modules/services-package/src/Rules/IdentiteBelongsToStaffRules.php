<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Staff;

/**
 * Class IdentiteBelongsToStaffRules
 * @package Satis2020\ServicePackage\Rules
 */
class IdentiteBelongsToStaffRules implements Rule
{

    protected $identiteId;

    public function __construct($identiteId)
    {
        $this->identite_id = $identiteId;
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
        if(!is_null($value)){

            return true;

        }

        if(!$staff = Staff::where('identite_id', $this->identite_id)->first()){

            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'L\'identité est associé à un staff, l\'adresse email ne peut pas être vide.';
    }

}
