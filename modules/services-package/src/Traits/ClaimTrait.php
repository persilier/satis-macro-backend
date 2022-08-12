<?php

namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\In;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Notifications\RejectAClaim;
use Satis2020\ServicePackage\Repositories\TreatmentRepository;

/**
 * Trait ClaimAwaitingTreatment
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimTrait
{


    /**
     * @param $claimId
     * @param bool $withRelations
     * @return Builder|Builder[]|Collection|Model|null
     */
    protected function getOneClaimQuery($claimId,$withRelations=true)
    {
        $relations = $withRelations?$this->getRelations():[];
        return Claim::with($relations)->findOrFail($claimId);
    }

    /**
     * @return array
     */
    protected function getRelations()
    {
        return Constants::getClaimRelations();
    }


}
