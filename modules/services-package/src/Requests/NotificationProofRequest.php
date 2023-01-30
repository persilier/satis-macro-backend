<?php

namespace Satis2020\ServicePackage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use Satis2020\ServicePackage\Consts\NotificationConsts;

/**
 * Class ActivityLogFilterRequest
 * @package Satis2020\ServicePackage\Requests
 */
class NotificationProofRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'channel' => ['nullable',Rule::in([NotificationConsts::EMAIL_CHANNEL,NotificationConsts::SMS_CHANEL,])],
            'date_start' => ['date_format:Y-m-d',new RequiredIf($this->has('date_end'))],
            'date_end' => ['date_format:Y-m-d','after_or_equal:date_start',new RequiredIf($this->has('date_start'))]
        ];
    }

}