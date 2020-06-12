<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\Validator;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Claim;

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

}