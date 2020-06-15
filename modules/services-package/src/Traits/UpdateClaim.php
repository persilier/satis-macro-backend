<?php


namespace Satis2020\ServicePackage\Traits;

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
use Satis2020\ServicePackage\Rules\ChannelIsForResponseRules;
use Satis2020\ServicePackage\Rules\EmailArray;
use Satis2020\ServicePackage\Rules\TelephoneArray;
use Satis2020\ServicePackage\Rules\UnitBelongsToInstitutionRules;
use Satis2020\ServicePackage\Rules\UnitCanBeTargetRules;
use Satis2020\ServicePackage\Rules\AccountBelongsToInstitutionRules;
trait UpdateClaim
{

    /**
     * @param $status| Claim complete - status=full | Claim incomplete - status=incomplete
     * @param $institution_id | Id institution
     * @return array
     * @throws CustomException
     */
    protected function getAllClaimCompleteOrIncomplete($institution_id, $status='full')
    {
        try {
            $claims = Claim::with([
                'claimObject',
                'claimer',
                'relationship',
                'accountTargeted',
                'institutionTargeted',
                'unitTargeted',
                'requestChannel',
                'responseChannel',
                'amountCurrency',
                'createdBy.identite',
                'completedBy.identite'
            ])->where(function ($query) use ($institution_id){
                $query->whereHas('createdBy', function ($q) use ($institution_id){
                    $q->where('institution_id', $institution_id);
                });
            })->where('status', $status)->get();
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer les listes des réclamations");
        }
        return $claims;
    }

    /**
     * @param $status| Claim complete - status=full | Claim incomplete - status=incomplete
     * @param $id | Id claim
     * @param $institution_id | Id institution
     * @return array
     * @throws CustomException
     */
    protected function getOneClaimCompleteOrIncomplete($institution_id, $id, $status='full')
    {
        try {
            $claim = Claim::with([
                'claimObject',
                'claimer',
                'relationship',
                'accountTargeted',
                'institutionTargeted',
                'unitTargeted',
                'requestChannel',
                'responseChannel',
                'amountCurrency',
                'createdBy.identite',
                'completedBy.identite'
            ])->where(function ($query) use ($institution_id){
                $query->whereHas('createdBy', function ($q) use ($institution_id){
                    $q->where('institution_id', $institution_id);
                });
            })->where('status', $status)->findOrFail($id);
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer cette réclamation");
        }
        return $claim;
    }

    /**
     * @param $claim
     * @return array
     * @throws CustomException
     */
    protected function getDataEdit($claim){
        $datas = [
            'claim' => $claim,
            'claimCategories' => ClaimCategory::all(),
            'institutions' => Institution::all(),
            'responseChannels' => Channel::where('is_response', 1)->get(),
            'requestChannels' => Channel::all(),
            'claimObjects' => ClaimObject::all(),
            'currencies' => Currency::all(),
        ];

        try {
            $institutionId = $claim->institution_targeted_id;
            $identiteId = $claim->claimer_id;
            $accounts = Account::with([
                'accountType',
            ])->where(function ($query) use ($institutionId, $identiteId){
                $query->whereHas('client_institution', function ($q) use ($institutionId, $identiteId){
                    $q->where('institution_id', $institutionId)
                     ->whereHas('client', function ($p) use ($identiteId){
                         $p->where('identites_id', $identiteId);
                    });
                });
            })->get();
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer les informations nécessaires à la modification d'une réclamation.");
        }

        if(!is_null($accounts))
            $datas['accounts'] = $accounts;

        return $datas;
    }

    protected function getClaimUpdate($institutionId, $claimId, $status = 'full'){
        try {
            $claim = Claim::where(function ($query) use ($institutionId){
                $query->whereHas('createdBy', function ($q) use ($institutionId){
                    $q->where('institution_id', $institutionId);
                });
            })->where('status', $status)->findOrFail($claimId);
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer cette réclamation");
        }
        return $claim;
    }


    protected function rulesUpdate($request)
    {
        $data = [
            'description' => 'required|string',
            'claim_object_id' => 'required|exists:claim_objects,id',
            'claimer_id' => 'required|exists:identites,id',
            'institution_targeted_id' => 'required|exists:institutions,id',
            'request_channel_slug' => 'required|exists:channels,slug',
            'response_channel_slug' => ['exists:channels,slug', new ChannelIsForResponseRules],
            'event_occured_at' => 'date_format:Y-m-d H:i',
            'account_targeted_id' => ['exists:accounts,id', new AccountBelongsToInstitutionRules($request->institution_targeted_id)],
            'amount_disputed' => 'integer',
            'amount_currency_slug' => 'exists:currencies,slug',
            'relationship_id' => 'required|exists:relationships,id',
            'unit_targeted_id'  => ['exists:units,id', new UnitBelongsToInstitutionRules($request->institution_targeted_id), new UnitCanBeTargetRules],
            'is_revival' => 'required|boolean',
            'created_by' => 'required|exists:staff,id',
        ];

        return $data;
    }


    protected function getData($request)
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
            'claimer_expectation',
            'account_targeted_id',
            'relationship_id',
            'unit_targeted_id'
        ];

        return $data;
    }


    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return string
     * @throws CustomException
     */
    protected function getStatus($request)
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
        $validator = Validator::make($request->only($this->getData($request)), $rules->all());

        if ($validator->fails()) {
            throw new CustomException("Toutes les exigeances pour cet objet de plainte ne sont pas renseignées.");
        }

        return $status;
    }

    /**
     * @param $claim
     * @return $request
     * @return $status
     */
    protected function updateClaim($request, $claim, $userId){

        foreach($request->all() as $key => $value){
            if(!isset($claim->{$key})) $claim->{$key} = $value;
        }

        $claim->status = $request->status;
        $claim->completed_by = $userId;
        $claim->completed_at = Carbon::now();
        $claim->save();
        return $claim;
    }

}