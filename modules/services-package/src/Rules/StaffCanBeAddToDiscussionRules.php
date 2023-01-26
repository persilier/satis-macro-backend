<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\Discussion;
use Satis2020\ServicePackage\Traits\Metadata;

class StaffCanBeAddToDiscussionRules implements Rule
{

    use Discussion, Metadata;

    protected $discussion;

    public function __construct($discussion)
    {
        $this->discussion = $discussion;
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
        $config = $config = $this->getMetadataByName('allow-pilot-collector-to-discussion');

        return (int)$config->allow_collector === 1 ? $this->getContributorsWithClaimCreator($this->discussion)->search(function ($item, $key) use ($value) {
            return $item->id == $value;
        }) !== false
            : $this->getContributors($this->discussion)->search(function ($item, $key) use ($value) {
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
        return 'This staff does not belong to the list of the possible contributors of the discussion';
    }
}
