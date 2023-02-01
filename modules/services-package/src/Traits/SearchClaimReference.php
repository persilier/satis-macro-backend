<?php

namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimObject;

/**
 * Trait SearchClaimReference
 * @package Satis2020\ServicePackage\Traits
 */
trait SearchClaimReference
{

    /**
     * @param $reference
     * @param bool $my
     * @return Builder[]|Collection
     */
    protected function searchClaim($reference, $my = false){

        $claim = Claim::with($this->getRelations());

        if($my){

            $claim = $claim->where('institution_targeted_id', $this->institution()->id);
        }

        return $claim->where('reference', $reference)->get();

    }


    /**
     * @return array
     */
    protected function getRelations()
    {
        return [
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
            'files',
            'activeTreatment.satisfactionMeasuredBy.identite',
            'activeTreatment.responsibleStaff.identite',
            'activeTreatment.assignedToStaffBy.identite',
            'activeTreatment.responsibleUnit',
            'activeTreatment.transferredToUnitBy'
        ];
    }

}
