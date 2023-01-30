<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Models\Metadata;

class ComponentDoesNotExistRules implements Rule
{
    protected $componentId;

    public function __construct($componentId = NULL)
    {
        $this->componentId = $componentId;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */

    public function passes($attribute, $value)
    {
        return collect(json_decode(Metadata::where('name', 'components-parameters')->first()->data))
                ->search(function ($item, $key) use ($value) {
                    return is_null($this->componentId)
                        ? $key == $value
                        : $key == $value && $this->componentId != $item['id'];
                }) === false;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'This component already exists';
    }

}
