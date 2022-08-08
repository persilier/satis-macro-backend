<?php
namespace Satis2020\Webhooks\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Satis2020\Webhooks\Consts\Event;
use Satis2020\Webhooks\Rules\UniqueWebhookRule;

trait ValidateRequestTrait
{

    public function validateRequest($request)
    {
        $validator = Validator::make($request->all(),[
            'name' => ["string",'required','unique:webhooks,name'],
            'url' => ["required","url",'unique:webhooks,name'],
            'institution_id' => ['required','exists:institutions,id'],
            'event' => ['required','string',Rule::in(Event::getEventsValues()),new UniqueWebhookRule($this)],
        ]);

        if ($validator->fails()){
            return response($validator->errors(),422);
        }

        return  true;
    }
}