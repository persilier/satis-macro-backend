<?php

namespace Satis2020\ServicePackage\Rules;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Unit;

class ChannelIsForResponseRules implements Rule
{

    public function __construct()
    {

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
        $channel = Channel::where('slug', $value)->firstOrFail();
        try{
            $condition = $channel->is_response == '1';
        }catch (\Exception $exception){
            throw new CustomException("Can't retrieve the can_be_target attribute of the channel");
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
        return ':attribute is not a response channel';
    }

}
