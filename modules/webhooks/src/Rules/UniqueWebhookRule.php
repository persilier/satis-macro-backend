<?php

namespace Satis2020\Webhooks\Rules;
use Illuminate\Contracts\Validation\Rule;
use Satis2020\Webhooks\Services\WebhookConfigService;

class UniqueWebhookRule implements Rule
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
        return (new WebhookConfigService)
            ->getByEvent($this->request->event,$this->request->institution_id,$this->request->id) == null;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'Un webhook pour ce événement est déjà configuré';
    }

}
