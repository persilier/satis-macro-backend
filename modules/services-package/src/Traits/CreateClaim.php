<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Rules\AccountBelongsToInstitutionRules;
use Satis2020\ServicePackage\Rules\ChannelIsForResponseRules;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Rules\UnitBelongsToInstitutionRules;
use Satis2020\ServicePackage\Rules\UnitCanBeTargetRules;
use Faker\Factory as Faker;

trait CreateClaim
{
    protected function rules($request, $with_client = true, $with_relationship = false, $with_unit = true)
    {
        $data = [
            'description' => 'required|string',
            'claim_object_id' => 'required|exists:claim_objects,id',
            'institution_targeted_id' => 'required|exists:institutions,id',
            'request_channel_slug' => 'required|exists:channels,slug',
            'response_channel_slug' => ['exists:channels,slug', new ChannelIsForResponseRules],
            'event_occured_at' => 'date_format:Y-m-d H:i',
            'amount_disputed' => 'integer',
            'amount_currency_slug' => 'exists:currencies,slug',
            'is_revival' => 'required|boolean',
            'created_by' => 'required|exists:staff,id'
        ];

        if ($with_client) {
            $data['claimer_id'] = 'nullable|exists:identites,id';
            $data['firstname'] = [Rule::requiredIf(is_null($request->claimer_id))];
            $data['lastname'] = [Rule::requiredIf(is_null($request->claimer_id))];
            $data['sexe'] = [Rule::requiredIf(is_null($request->claimer_id)), Rule::in(['M', 'F', 'A'])];
            $data['telephone'] = [Rule::requiredIf(is_null($request->claimer_id)), 'array', new TelephoneArray];
            $data['email'] = [Rule::requiredIf(is_null($request->claimer_id)), 'array', new EmailArray];
            $data['account_targeted_id'] = ['exists:accounts,id', new AccountBelongsToInstitutionRules($request->institution_targeted_id)];
        } else {
            $data['firstname'] = 'required';
            $data['lastname'] = 'required';
            $data['sexe'] = ['required', Rule::in(['M', 'F', 'A'])];
            $data['telephone'] = ['required', 'array', new TelephoneArray];
            $data['email'] = ['required', 'array', new EmailArray];
        }

        if ($with_relationship) {
            $data['relationship_id'] = 'required|exists:relationships,id';
        }

        if ($with_unit) {
            $data['unit_targeted_id'] = ['exists:units,id', new UnitBelongsToInstitutionRules($request->institution_targeted_id), new UnitCanBeTargetRules];
        }

        return $data;
    }

    protected function createReference()
    {
        $faker = Faker::create();
        return date('Y') . date('m') . '-' . $faker->randomNumber(6, true);
    }

    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return string
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
            throw new CustomException("Can't retrieve the claimObject requirements");
        }

        $status = 'full';
        $validator = Validator::make($request->only($this->getData($request, $with_client, $with_relationship, $with_unit)), $rules->all());
        if ($validator->fails()) {
            $status = 'incomplete';
        }

        return $status;
    }

    protected function getData($request, $with_client = true, $with_relationship = false, $with_unit = true)
    {
        $data = [
            'description',
            'claim_object_id',
            'claimer_id',
            'institution_targeted_id',
            'request_channel_slug',
            'response_channel_slug',
            'event_occured_at',
            'amount_disputed',
            'amount_currency_slug',
            'is_revival',
            'created_by',
            'status',
            'reference',
            'claimer_expectation'
        ];

        if ($with_client) {
            $data[] = 'account_targeted_id';
        }

        if ($with_relationship) {
            $data[] = 'relationship_id';
        }

        if ($with_unit) {
            $data[] = 'unit_targeted_id';
        }

        return $data;
    }


    protected function createClaim($request, $with_client = true, $with_relationship = false, $with_unit = true)
    {
        return $claim = Claim::create($request->only($this->getData($request, $with_client, $with_relationship, $with_unit)));
    }

}