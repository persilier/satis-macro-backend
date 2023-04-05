<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ProcessNotifyAllActivePilot;
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
use Satis2020\Webhooks\Consts\Event;
use Satis2020\Webhooks\Facades\SendEvent;
use Satis2020\ServicePackage\Notifications\RegisterAClaim;
use Satis2020\ServicePackage\Rules\ChannelIsForResponseRules;
use Satis2020\ServicePackage\Rules\AccountBelongsToClientRules;
use Satis2020\ServicePackage\Rules\UnitBelongsToInstitutionRules;
use Satis2020\ServicePackage\Notifications\ReminderBeforeDeadline;
use Satis2020\ServicePackage\Notifications\AcknowledgmentOfReceipt;
use Satis2020\ServicePackage\Rules\ClientBelongsToInstitutionRules;
use Satis2020\ServicePackage\Notifications\RegisterAClaimHighForcefulness;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ConfigurationPilotTrait;

/**
 * Trait CreateClaim
 * @package Satis2020\ServicePackage\Traits
 */
trait CreateClaim
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
    protected function rules($request, $with_client = true, $with_relationship = false, $with_unit = true, $update = false)
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
                    try {
                        if (Carbon::parse($value)->gt(Carbon::now())) {
                            $fail($attribute . ' is invalid! The value is greater than now');
                        }
                    } catch (InvalidFormatException $e) {
                        $fail($attribute . ' ne correspond pas au format Y-m-d H:i.');
                    }
                },
                'before_or_equal:' . now()->format('Y-m-d H:i')
            ],
            'amount_disputed' => ['nullable', 'filled', 'integer', 'min:1', Rule::requiredIf($request->filled('amount_currency_slug'))],
            'amount_currency_slug' => ['nullable', 'filled', 'exists:currencies,slug', Rule::requiredIf($request->filled('amount_disputed'))],
            'is_revival' => 'required|boolean',
            'created_by' => 'required|exists:staff,id',
            'file.*' => 'max:20000|mimes:doc,pdf,docx,txt,jpeg,bmp,png,xls,xlsx,csv',
            'attach_files' => 'nullable',
            'account_number' => 'filled'
        ];

        if ($with_client) {
            $data['claimer_id'] = ['nullable', 'filled', 'exists:identites,id', new ClientBelongsToInstitutionRules($request->institution_targeted_id)];
            $data['firstname'] = [Rule::requiredIf($request->isNotFilled('claimer_id'))];
            $data['lastname'] = [Rule::requiredIf($request->isNotFilled('claimer_id'))];
            $data['raison_sociale'] = 'nullable';
            $data['type_client'] = 'required';
            $data['sexe'] = [Rule::requiredIf($request->isNotFilled('claimer_id')), Rule::in(['M', 'F', 'A'])];
            $data['telephone'] = ["required", 'array', new TelephoneArray];
            $data['email'] = [Rule::requiredIf($request->response_channel_slug === "email"), 'array', new EmailArray];
            $data['email.*'] = ["email"];
            $data['account_targeted_id'] = ['exists:accounts,id', new AccountBelongsToClientRules($request->institution_targeted_id, $request->claimer_id)];
        } else {
            $data['firstname'] = 'required';
            $data['lastname'] = 'required';
            $data['raison_sociale'] = 'nullable';
            $data['type_client'] = 'required';
            $data['sexe'] = ['required', Rule::in(['M', 'F', 'A'])];
            $data['telephone'] = ['required', 'array', new TelephoneArray];
            $data['email'] = [Rule::requiredIf($request->response_channel_slug === "email"), 'array', new EmailArray];
            $data['email.*'] = ["email"];
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


    /**
     * @param $institution_targeted_id
     * @return string
     */
    protected function createReference($institution_targeted_id)
    {
        $institutionTargeted = Institution::with('institutionType')->findOrFail($institution_targeted_id);

        $appNature = substr($this->getAppNature($institution_targeted_id), 0, 2);

        $claimsNumber = Claim::withTrashed()
            ->whereBetween('created_at', [
                Carbon::now()->startOfYear()->format('Y-m-d H:i:s'),
                Carbon::now()->endOfYear()->format('Y-m-d H:i:s')
            ])
            ->where('institution_targeted_id', $institution_targeted_id)
            ->get()
            ->count() + 1;

        $formatClaimsNumber = str_pad("{$claimsNumber}", 6, "0", STR_PAD_LEFT);

        return strtoupper('SATIS' . $appNature . '-' . date('Y') . date('m') . $formatClaimsNumber . '-' . $institutionTargeted->acronyme);
    }

    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return array
     * @throws CustomException
     */
    protected function getStatus($request, $with_client = true, $with_relationship = false, $with_unit = true)
    {
        try {
            $requirements = ClaimObject::with('requirements')
                ->where('id', $request->claim_object_id)
                ->firstOrFail()
                ->requirements
                ->pluck('name');
            $rules = collect([]);

            foreach ($requirements as $requirement) {
                $rules->put($requirement, 'required');
            }
        } catch (\Exception $exception) {

            throw new CustomException(__('errors.retrieve_claim_object', [], app()->getLocale()));
        }

        $status = 'full';

        $validator = Validator::make($request->only($this->getData($request, $with_client, $with_relationship, $with_unit)), $rules->all());

        $errors = [];

        if ($validator->fails()) {

            $errors = $validator->errors()->messages();
            $status = 'incomplete';
        } else {
            // status = full so the claim is complete
            $request->merge(['completed_by' => $request->created_by, 'completed_at' => Carbon::now()]);
        }

        return ['status' => $status, 'errors' => $this->incompleteErrors($errors)];
    }


    /**
     * @param $errors
     * @return \Illuminate\Support\Collection
     */
    protected function incompleteErrors($errors)
    {

        $requirements = collect([]);

        if (!empty($errors)) {
            foreach ($errors as $key => $error) {

                ($requirement = Requirement::where('name', $key)->first()) ? $requirements->push($requirement) : '';
            }
        }
        return $requirements;
    }

    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return array
     */
    protected function getData($request, $with_client = true, $with_relationship = false, $with_unit = true)
    {
        $data = [
            'description',
            'claim_object_id',
            'lieu',
            'claimer_id',
            'institution_targeted_id',
            'request_channel_slug',
            'response_channel_slug',
            'event_occured_at',
            'amount_disputed',
            'is_revival',
            'created_by',
            'status',
            'reference',
            'claimer_expectation',
            'account_number'
        ];


        if ($request->has('amount_disputed')) {
            if ($request->amount_disputed >= 1) {
                $data[] = 'amount_currency_slug';
            }
        }

        if ($request->has('status')) {
            if ($request->status == 'full') {
                $data[] = 'completed_by';
                $data[] = 'completed_at';
            }
        }

        if ($with_client) {
            $data[] = 'account_targeted_id';
        }

        if ($with_relationship) {
            $data[] = 'relationship_id';
        }

        if ($with_unit) {
            $data[] = 'unit_targeted_id';
        }


        $data = array_merge($data, ['time_limit', 'time_unit', 'time_staff', 'time_treatment', 'time_validation', 'time_measure_satisfaction']);
        $time_object = ClaimObject::find($request->claim_object_id);

        $request->merge([

            'time_limit' => $time_object->time_limit,
            'time_unit' => $time_object->time_unit,
            'time_staff' => $time_object->time_staff,
            'time_treatment' => $time_object->time_treatment,
            'time_validation' => $time_object->time_validation,
            'time_measure_satisfaction' => $time_object->time_measure_satisfaction,
        ]);

        return $data;
    }


    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return mixed
     */
    protected function createClaim($request, $with_client = true, $with_relationship = false, $with_unit = true)
    {
        $data = $request->only($this->getData($request, $with_client, $with_relationship, $with_unit));

        $claim = Claim::create($data);
        $this->uploadAttachments($request, $claim);

        // send notification to claimer
        if (!is_null($claim->claimer)) {
            $claim->claimer->notify(new AcknowledgmentOfReceipt($claim));
        }


        if (is_null($claim->createdBy)) {
            $institutionTargeted = $claim->institutionTargeted;
        } else {
            $institutionTargeted = $claim->createdBy->institution;
        }
        // send notification to pilot
        //if (!is_null($claim->createdBy)) {
        if (!is_null($institutionTargeted)) {
            $configAnyPilotActif = $this->getConfigurationAnyPilotActif($institutionTargeted);
            $severityLevel = "normal";
            if ($claim->claimObject->severityLevel && ($claim->claimObject->severityLevel->status === 'high')) {
                $severityLevel = "high";
            }
            if ($configAnyPilotActif && $configAnyPilotActif->many_active_pilot) {
                \Illuminate\Support\Facades\Notification::send(
                    $this->getAllIdentitePilotActif($institutionTargeted),
                    $severityLevel === "high" ? new RegisterAClaimHighForcefulness($claim) : new RegisterAClaim($claim)
                );
            } else {
                if (!is_null($this->getInstitutionPilot($institutionTargeted))) {
                    $this->getInstitutionPilot($institutionTargeted)->notify($severityLevel === "high" ? new RegisterAClaimHighForcefulness($claim) : new RegisterAClaim($claim));
                }
                $this->closeTimeLimitNotification($claim);
                // send recurrence notification to the pilot
                $this->recurrenceNotification($claim);
            }
            // if (!is_null($this->getInstitutionPilot($institutionTargeted))) {
            //     $severityLevel = "normal";
            //     if($claim->claimObject->severityLevel && ($claim->claimObject->severityLevel->status === 'high')) {
            //         $this->getInstitutionPilot($institutionTargeted)->notify(new RegisterAClaimHighForcefulness($claim));
            //         $severityLevel = "high";
            //     } else {
            //         $this->getInstitutionPilot($institutionTargeted)->notify(new RegisterAClaim($claim));
            //     }

            //sending webhook event
            SendEvent::sendEvent(Event::CLAIM_REGISTERED, $claim->toArray(), $claim->institution_targeted_id);
            //     $process_notify_all_active_pilot = new ProcessNotifyAllActivePilot($claim,$severityLevel, Auth::user()->id);
            //     dispatch($process_notify_all_active_pilot);

            //     // check if the claimObject related to the claim have a time_limit = 1 and send a notification
            //     $this->closeTimeLimitNotification($claim);

            //     // send recurrence notification to the pilot
            //     $this->recurrenceNotification($claim);
            // }
        }
        //}
        return $claim;
    }

    /**
     * @param $request
     * @param $claim
     */
    protected function uploadAttachments($request, $claim)
    {
        if ($request->hasfile('file')) {
            foreach ($request->file('file') as $file) {

                $title = $file->getClientOriginalName();
                $path = $file->store('claim-attachments', 'public');
                $url = Storage::url("$path");

                // insert the file into database
                $claim->files()->create(['title' => $title, 'url' => $url, 'attach_at' => $claim->status == Claim::CLAIM_ASSIGNED_TO_STAFF || ($claim->status == Claim::CLAIM_UNSATISFIED && $claim->escalation_status == Claim::CLAIM_ASSIGNED_TO_STAFF) ? File::ATTACH_AT_TREATMENT : null]);
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getTargetedInstitutions()
    {
        return Institution::with('institutionType')
            ->get()
            ->filter(function ($value, $key) {
                return $value->institutionType->name != 'holding' && $value->institutionType->name != 'observatory';
            })
            ->values();
    }

    /**
     * @param $claim
     */
    protected function closeTimeLimitNotification($claim)
    {
        // check if the claimObject related to the claim have a time_limit = 1 and send a notification to the active pilot
        if (!is_null($claim->claimObject)) {
            if ($claim->claimObject->time_limit == 1) {
                $this->getInstitutionPilot(is_null($claim->createdBy) ? $claim->institutionTargeted :
                    $claim->createdBy->institution)->notify(new ReminderBeforeDeadline($claim, $claim->claimObject->time_limit));
            }
        }
    }

    /**
     * @param $claim
     */
    protected function recurrenceNotification($claim)
    {
        if ($this->canSendRecurrenceNotification(is_null($claim->createdBy) ? $claim->institution_targeted_id :
            $claim->createdBy->institution_id)) {
            $this->getInstitutionPilot(is_null($claim->createdBy) ? $claim->institutionTargeted :
                $claim->createdBy->institution)->notify(new Recurrence($claim));
        }
    }
}
