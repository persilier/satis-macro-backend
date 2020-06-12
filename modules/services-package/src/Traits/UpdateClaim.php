<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\Validator;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\ClaimCategory;
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

    /**
     * @param $claim
     * @return $attributes
     * @return $status
     */
    public function updateClaim($claim, array $attributes){
        /*foreach($claim as $key => $value){
            if(is_null($value)) $claim->{$key} = $attributes[$key];
        }
        $claim->status = 'full';
        return $claim->save();*/
    }

}