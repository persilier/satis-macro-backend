<?php
namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Claim;

trait ClaimAwaitingTreatment
{
    protected function getAllClaimAwaitingTreatment(){
        try {
            $claims = Claim::with($this->getRelations())
                ->where('institution_targeted_id',$institutionId)
                ->where('status', 'full')->get();
        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer les listes des réclamations");
        }
        return $claims;
    }

    protected function getRelations()
    {
        return [
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files'
        ];
    }

}