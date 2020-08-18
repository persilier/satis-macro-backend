<?php


namespace Satis2020\ServicePackage\Traits;

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

/**
 * Trait UpdateClaim
 * @package Satis2020\ServicePackage\Traits
 */
trait UpdateClaim
{
    /**
     * @param $status| Claim complete - status=full | Claim incomplete - status=incomplete
     * @param $institutionId | Id institution
     * @return array
     * @throws CustomException
     */
    protected function getAllClaimCompleteOrIncomplete($institutionId, $status ='full')
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
            ])->where(function ($query) use ($institutionId){
                $query->whereHas('createdBy', function ($q) use ($institutionId){
                    $q->where('institution_id', $institutionId);
                });
            })->where('status', $status)->get();

        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer les listes des réclamations");
        }
        return $claims;
    }


    /**
     * @param $status| Claim complete - status=full | Claim incomplete - status=incomplete
     * @param $institutionId | Id institution
     * @return array
     * @throws CustomException
     */
    protected function getAllClaimCompleteOrIncompleteForMyInstitution($institutionId, $status='full')
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
            ])->where('institution_targeted_id',$institutionId)->where('status', $status)->get();
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer les listes des réclamations");
        }
        return $claims;
    }

    /**
     * @param $status| Claim complete - status=full | Claim incomplete - status=incomplete
     * @param $claimId | Id claim
     * @param $institution_id | Id institution
     * @return array
     * @throws CustomException
     */
    protected function getOneClaimCompleteOrIncomplete($institution_id, $claimId, $status='full')
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
            ])->where(function ($query) use ($institution_id){
                $query->whereHas('createdBy', function ($q) use ($institution_id){
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
    protected function getOneClaimCompleteOrIncompleteForMyInstitution($institutionId, $claimId, $status='full')
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

    protected function getAllRequirements($claimObject){

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
    protected function getDataEdit($claim){
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


    /**
     * @param $claim
     * @return array
     * @throws CustomException
     */
    protected function getDataEditWithoutClient($claim){
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
    protected function getClaimUpdate($institutionId, $claimId, $status = 'full'){
        try {
            $claim = Claim::where(function ($query) use ($institutionId){
                $query->whereHas('createdBy', function ($q) use ($institutionId){
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
     */
    protected function getClaimUpdateForMyInstitution($institutionId, $claimId, $status = 'full'){
        try {
            $claim = Claim::where('institution_targeted_id',$institutionId)->where('status', $status)->findOrFail($claimId);
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer cette réclamation");
        }
        return $claim;
    }

    /**
     * @param $claim
     * @return $request
     * @return $userId
     * @throws CustomException
     */
    protected function updateClaim($request, $claim, $userId){

        if($request->status === 'incomplete'){
            throw new CustomException("Toutes les exigeances pour cet objet de plainte ne sont pas renseignées.");
        }

        foreach($request->all() as $key => $value){
            if(($claim->{$key}) && (!empty($claim->{$key})))
                $claim->{$key} = $value;
        }

        $claim->status = $request->status;
        $claim->completed_by = $userId;
        $claim->completed_at = Carbon::now();
        $claim->save();

        // send notification to pilot
        $this->getInstitutionPilot($claim->createdBy->institution)->notify(new RegisterAClaim($claim));

        return $claim;
    }



}