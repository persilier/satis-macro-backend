<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\Relationship;
use Satis2020\ServicePackage\Notifications\RegisterAClaim;
use Satis2020\ServicePackage\Rules\AccountBelongsToClientRules;
use Satis2020\ServicePackage\Rules\ChannelIsForResponseRules;
use Satis2020\ServicePackage\Rules\ClientBelongsToInstitutionRules;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\IdentiteBelongsToStaffRules;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Rules\UnitBelongsToInstitutionRules;
use Satis2020\ServicePackage\Rules\UnitCanBeTargetRules;

/**
 * Trait UpdateClaim
 * @package Satis2020\ServicePackage\Traits
 */
trait UpdateClaim
{

    /**
     * @param $request
     * @param $claim
     * @return void
     * @throws CustomException
     */
    protected function validateUnicityIdentiteCompletion($request, $claim)
    {

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleInArrayUnicityVerification($request->telephone, 'identites', 'telephone', 'id', $claim->claimer_id);

        if (!$verifyPhone['status']) {

            throw new CustomException("We can't perform your request. The phone number  belongs to someone else");

        }

        // Client Email Unicity Verification
        if ($request->has('email')) {

            $verifyEmail = $this->handleInArrayUnicityVerification($request->email, 'identites', 'email', 'id', $claim->claimer_id);

            if (!$verifyEmail['status']) {

                throw new CustomException("We can't perform your request. The email address  belongs to someone else");

            }

        }

    }

    /**
     * @param $request
     * @param $claim
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @param bool $update
     * @return array
     * @throws CustomException
     */
    protected function rulesCompletion($request, $claim, $with_client = false, $with_relationship = false, $with_unit = true, $update = true)
    {

        $data = [
            'description' => 'required|string',
            'claim_object_id' => 'required|exists:claim_objects,id',
            'institution_targeted_id' => 'required|exists:institutions,id',
            'request_channel_slug' => 'required|exists:channels,slug',
            'response_channel_slug' => ['exists:channels,slug', new ChannelIsForResponseRules],
            'event_occured_at' => 'date_format:Y-m-d H:i',
            'amount_disputed' => 'nullable|integer',
            'amount_currency_slug' => [Rule::requiredIf(!is_null($request->amount_disputed)), 'exists:currencies,slug'],
            'is_revival' => 'required|boolean',
            'file.*' => 'mimes:doc,pdf,docx,txt,jpeg,bmp,png,mp3,mp4'
        ];

        $data = $this->rules($request, $with_client, $with_relationship, $with_unit, $update);

        $rules = Arr::only($data, [
            'description', 'claim_object_id', 'institution_targeted_id', 'request_channel_slug',
            'response_channel_slug', 'event_occured_at', 'amount_disputed', 'amount_currency_slug',
            'is_revival', 'is_revival', 'file.*', 'firstname', 'lastname', 'sexe', 'telephone', 'email'
        ]);

        $rules['account_targeted_id'] = ['exists:accounts,id', new AccountBelongsToClientRules($request->institution_targeted_id, $request->claimer_id)];

        if ($with_relationship) {

            $rules['relationship_id'] = $data['relationship_id'];
        }

        if ($with_unit) {

            $rules['unit_targeted_id'] = $data['unit_targeted_id'];
        }

        try {

            $requirements = ClaimObject::with('requirements')
                ->where('id', $claim->claim_object_id)
                ->firstOrFail()
                ->requirements
                ->pluck('name');

            foreach ($requirements as $requirement) {

                $rules[$requirement] = 'required';
            }

        } catch (\Exception $exception) {

            throw new CustomException("Can't retrieve the claimObject requirements");

        }

        return $rules;
    }

    /**
     * @param $status | Claim complete - status=full | Claim incomplete - status=incomplete
     * @param $institutionId | Id institution
     * @return array
     * @throws CustomException
     */
    protected function getAllClaimCompleteOrIncomplete($institutionId, $status = 'full')
    {
        try {
            $claims = Claim::with([
                'claimObject.claimCategory',
                'claimer',
                'relationship',
                'accountTargeted',
                'institutionTargeted',
                'unitTargeted',
                'requestChannel',
                'responseChannel',
                'amountCurrency',
                'createdBy.identite',
                'completedBy.identite',
                'files'
            ])->where(function ($query) use ($institutionId) {
                $query->whereHas('createdBy', function ($q) use ($institutionId) {
                    $q->where('institution_id', $institutionId);
                });
            })->where('status', $status)->get();

        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer les listes des réclamations");
        }
        return $claims;
    }


    /**
     * @param $status | Claim complete - status=full | Claim incomplete - status=incomplete
     * @param $institutionId | Id institution
     * @return array
     * @throws CustomException
     */
    protected function getAllClaimCompleteOrIncompleteForMyInstitution($institutionId, $status = 'full')
    {
        try {
            $claims = Claim::with([
                'claimObject.claimCategory',
                'claimer',
                'relationship',
                'accountTargeted',
                'institutionTargeted',
                'unitTargeted',
                'requestChannel',
                'responseChannel',
                'amountCurrency',
                'createdBy.identite',
                'completedBy.identite',
                'files'
            ])->where('institution_targeted_id', $institutionId)->where('status', $status)->get();
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer les listes des réclamations");
        }
        return $claims;
    }

    /**
     * @param $status | Claim complete - status=full | Claim incomplete - status=incomplete
     * @param $claimId | Id claim
     * @param $institution_id | Id institution
     * @return array
     * @throws CustomException
     */
    protected function getOneClaimCompleteOrIncomplete($institution_id, $claimId, $status = 'full')
    {
        try {
            $claim = Claim::with([
                'claimObject.claimCategory',
                'claimer',
                'relationship',
                'accountTargeted',
                'institutionTargeted',
                'unitTargeted',
                'requestChannel',
                'responseChannel',
                'amountCurrency',
                'createdBy.identite',
                'completedBy.identite',
                'files'
            ])->where(function ($query) use ($institution_id) {
                $query->whereHas('createdBy', function ($q) use ($institution_id) {
                    $q->where('institution_id', $institution_id);
                });
            })->where('status', $status)->findOrFail($claimId);
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer cette réclamation");
        }
        return $claim;
    }

    /**
     * @param $institutionId | Id institution
     * @param $claimId
     * @param string $status | Claim complete - status=full | Claim incomplete - status=incomplete
     * @return array
     * @throws CustomException
     */
    protected function getOneClaimCompleteOrIncompleteForMyInstitution($institutionId, $claimId, $status = 'full')
    {
        try {
            $claim = Claim::with([
                'claimObject.claimCategory',
                'claimer',
                'relationship',
                'accountTargeted',
                'institutionTargeted',
                'unitTargeted',
                'requestChannel',
                'responseChannel',
                'amountCurrency',
                'createdBy.identite',
                'completedBy.identite',
                'files'
            ])->where('institution_targeted_id', $institutionId)->where('status', $status)->findOrFail($claimId);
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer cette réclamation");
        }
        return $claim;
    }

    protected function getAllRequirements($claimObject)
    {

        $requirements = $claimObject->requirements->pluck('name');
        $rules = collect([]);

        foreach ($requirements as $requirement) {
            $rules->put($requirement, 'required');
        }

        return $rules;

    }

    /**
     * @param $claim
     * @return array
     * @throws CustomException
     */
    protected function getDataEdit($claim)
    {

        $datas = [
            'claim' => $claim,
            'claimCategories' => ClaimCategory::all(),
            'institutions' => Institution::all(),
            'channels' => Channel::all(),
            'claimObjects' => ClaimObject::all(),
            'currencies' => Currency::all(),
            'requirements' => $this->getAllRequirements($claim->claimObject)
        ];

        try {

            $institutionId = $claim->institution_targeted_id;
            $identiteId = $claim->claimer_id;
            $accounts = Account::with([
                'accountType',
            ])->where(function ($query) use ($institutionId, $identiteId) {
                $query->whereHas('client_institution', function ($q) use ($institutionId, $identiteId) {
                    $q->where('institution_id', $institutionId)
                        ->whereHas('client', function ($p) use ($identiteId) {
                            $p->where('identites_id', $identiteId);
                        });
                });
            })->get();

        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer les informations nécessaires à la modification d'une réclamation.");
        }

        if (!is_null($accounts))
            $datas['accounts'] = $accounts;

        return $datas;
    }


    /**
     * @param $claim
     * @return array
     * @throws CustomException
     */
    protected function getDataEditWithoutClient($claim)
    {
        $datas = [
            'claim' => $claim,
            'claimCategories' => ClaimCategory::all(),
            'institutions' => Institution::all(),
            'channels' => Channel::all(),
            'claimObjects' => ClaimObject::all(),
            'currencies' => Currency::all(),
            'relationships' => Relationship::all(),
            'requirements' => $this->getAllRequirements($claim->claimObject)
        ];

        return $datas;
    }

    /**
     * @param $institutionId
     * @param $claimId
     * @param string $status
     * @return mixed
     * @throws CustomException
     */
    protected function getClaimUpdate($institutionId, $claimId, $status = 'full')
    {
        try {
            $claim = Claim::where(function ($query) use ($institutionId) {
                $query->whereHas('createdBy', function ($q) use ($institutionId) {
                    $q->where('institution_id', $institutionId);
                });
            })->where('status', $status)->findOrFail($claimId);
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer cette réclamation.");
        }
        return $claim;
    }


    /**
     * @param $institutionId
     * @param $claimId
     * @param string $status
     * @return mixed
     * @throws CustomException
     */
    protected function getClaimUpdateForMyInstitution($institutionId, $claimId, $status = 'full')
    {
        try {
            $claim = Claim::where('institution_targeted_id', $institutionId)->where('status', $status)->findOrFail($claimId);
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer cette réclamation");
        }
        return $claim;
    }

    /**
     * @param $request
     * @param $claim
     * @param $userId
     * @return mixed $request
     * @throws CustomException
     */
    protected function updateClaim($request, $claim, $userId)
    {

        if ($request->status === 'incomplete') {

            throw new CustomException("Toutes les exigeances pour cet objet de plainte ne sont pas renseignées.");

        }

        foreach ($request->all() as $key => $value) {

            if (($claim->{$key}) && (!empty($claim->{$key})))
                $claim->{$key} = $value;

        }

        $claim->status = 'full';
        $claim->completed_by = $userId;
        $claim->completed_at = Carbon::now();
        $claim->save();

        $claim->claimer->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email']));
        // send notification to pilot
        try {
            $this->getInstitutionPilot($claim->createdBy->institution)->notify(new RegisterAClaim($claim));
        } catch (\Exception $exception) {
        }

        return $claim;
    }


}