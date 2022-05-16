<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Unit;

class UniqueTelephoneRule implements Rule
{

    /**
     * @var null
     */
    private $skipId;

    public function __construct($skipId=null)
    {

        $this->skipId = $skipId;
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

        return Identite::query()
                ->when($this->skipId!=null,function (Builder $builder){
                    $builder->where('id',"<>",$this->skipId);
                })
                ->whereJsonContains("telephone",$value)
                ->first()==null;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return __('validation.unique_telephone');
    }

}
