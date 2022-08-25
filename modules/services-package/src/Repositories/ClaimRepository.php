<?php

namespace Satis2020\ServicePackage\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Claim;

class ClaimRepository
{
    /***
     * @var $claim
     */
    private $claim;

    public function __construct(Claim $claim)
    {
        $this->claim = $claim;
    }

    /***
     * @return Builder[]|Collection
     */
    public function getAllClaims()
    {
        return $this->claim->newQuery()->get();
    }

    /***
     * @return Builder[]|Collection
     */
    public function getAllClaimsWithRelations()
    {
        return $this->claim->newQuery()->with(Constants::getClaimRelations())->get();
    }
    /***
     * @return Builder[]|Collection
     */
    public function getAllClaimsRevokedWithRelations()
    {
        return $this->claim->newQuery()->with(Constants::getClaimRelations())
            ->whereHas('activeTreatment',function ($query){
                $query->whereNotNull('rejected_at');
            })
            ->get();
    }
}