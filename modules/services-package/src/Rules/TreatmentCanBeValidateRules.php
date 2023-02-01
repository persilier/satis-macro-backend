<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\AwaitingValidation;
use Satis2020\ServicePackage\Traits\DataUserNature;

class TreatmentCanBeValidateRules implements Rule
{
    use AwaitingValidation, DataUserNature;

    public $institution_id;
    /**
     * @var string
     */
    private $type;

    public function __construct($institution_id,$type="normal")
    {
        $this->institution_id = $institution_id;
        $this->type = $type;
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

        $claims = $this->getClaimsAwaitingValidationInMyInstitution(false,false,null,$this->type,$this->institution_id);

        return $claims->search(function ($item, $key) use ($value) {
            return $item->id == $value;
        }) !== false;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The claim treatment is already validated or it can not be validated by this pilot';
    }
}
