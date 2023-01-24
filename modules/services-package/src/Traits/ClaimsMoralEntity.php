<?php


namespace Satis2020\ServicePackage\Traits;


use App\Jobs\ProcessNotifyAllActivePilot;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ConfigurationPilotTrait;
use Satis2020\ServicePackage\Models\Claim;
use Carbon\Exceptions\InvalidFormatException;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Requirement;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Notifications\Recurrence;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Rules\UnitCanBeTargetRules;
use Satis2020\ServicePackage\Notifications\RegisterAClaim;
use Satis2020\ServicePackage\Rules\ChannelIsForResponseRules;
use Satis2020\ServicePackage\Rules\AccountBelongsToClientRules;
use Satis2020\ServicePackage\Rules\UnitBelongsToInstitutionRules;
use Satis2020\ServicePackage\Notifications\ReminderBeforeDeadline;
use Satis2020\ServicePackage\Notifications\AcknowledgmentOfReceipt;
use Satis2020\ServicePackage\Rules\ClientBelongsToInstitutionRules;
use Satis2020\ServicePackage\Notifications\RegisterAClaimHighForcefulness;

/**
 * Trait ClaimsMoralEntity
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimsMoralEntity
{
    use Notification, ConfigurationPilotTrait;
    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @param bool $update
     * @return array
     */
    protected function rulesForMoralEntity($request, $with_client = true, $with_relationship = false, $with_unit = true, $update = false)
    {
        $data = [
            'description' => 'required|string',
            'claim_object_id' => 'required|exists:claim_objects,id',
            'institution_targeted_id' => 'required|exists:institutions,id',
            'request_channel_slug' => 'required|exists:channels,slug',
            'response_channel_slug' => ['required', 'exists:channels,slug', new ChannelIsForResponseRules],
            'lieu' => 'nullable|string',
            'event_occured_at' => [
                'required',
                'date_format:Y-m-d H:i',
                function ($attribute, $value, $fail) {
                    try{
                        if (Carbon::parse($value)->gt(Carbon::now())) {
                            $fail($attribute . ' is invalid! The value is greater than now');
                        }
                    }catch (InvalidFormatException $e){
                        $fail($attribute . ' ne correspond pas au format Y-m-d H:i.');
                    }
                }
            ],
            'amount_disputed' => ['nullable','filled','integer', 'min:1' , Rule::requiredIf($request->filled('amount_currency_slug'))],
            'amount_currency_slug' => ['nullable','filled', 'exists:currencies,slug', Rule::requiredIf($request->filled('amount_disputed'))],
            'is_revival' => 'required|boolean',
            'created_by' => 'required|exists:staff,id',
            'file.*' => 'max:20000|mimes:doc,pdf,docx,txt,jpeg,bmp,png,xls,xlsx,csv',
            'attach_files' => 'nullable',
            'account_number'=>'filled'
        ];

        if ($with_client) {
            $data['claimer_id'] = ['nullable','filled', 'exists:identites,id', new ClientBelongsToInstitutionRules($request->institution_targeted_id)];
            $data['raison_sociale'] = [Rule::requiredIf($request->isNotFilled('claimer_id'))];
            $data['telephone'] = ["required", 'array', new TelephoneArray];
            $data['email'] = [Rule::requiredIf($request->response_channel_slug === "email"), 'array', new EmailArray];
            $data['account_targeted_id'] = ['exists:accounts,id', new AccountBelongsToClientRules($request->institution_targeted_id, $request->claimer_id)];
        } else {
            $data['raison_sociale'] = 'required';
            $data['telephone'] = ['required', 'array', new TelephoneArray];
            $data['email'] = [Rule::requiredIf($request->response_channel_slug === "email"), 'array', new EmailArray];
        }

        if ($with_relationship) {
            $data['relationship_id'] = 'required|exists:relationships,id';
        }

        if ($with_unit) {
            $data['unit_targeted_id'] = ['nullable', 'exists:units,id', new UnitBelongsToInstitutionRules($request->institution_targeted_id), new UnitCanBeTargetRules];
        }

        if ($update) {
            unset($data['created_by']);
        }

        return $data;
    }
   

}
