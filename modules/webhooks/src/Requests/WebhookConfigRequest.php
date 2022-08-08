<?php

namespace Satis2020\Webhooks\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Satis2020\Webhooks\Consts\Event;
use Satis2020\Webhooks\Rules\UniqueWebhookRule;

class WebhookConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ["string",'required','unique:webhooks,name'],
            'url' => ["required","url",'unique:webhooks,name'],
            'institution_id' => ['required','exists:institutions,id'],
            'event' => ['required','string',Rule::in(Event::getEventsValues()),new UniqueWebhookRule($this)],
        ];
    }
}
