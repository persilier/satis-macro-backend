<?php

namespace Satis2020\Escalation\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\Escalation\Services\TreatmentBoardService;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Services\StateService;

class StandardBoardExists implements Rule
{


    private $request;

    public function __construct($request)
    {
        $this->request = $request;
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
        $treatmentBoardService = new TreatmentBoardService;

        if ($value == TreatmentBoard::SPECIFIC) {
            $valid = true;
        } else {
            $valid = !is_null($treatmentBoardService->getStandardBoard());
        }
        return $valid;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'Le commité standad existe déjà';
    }
}
