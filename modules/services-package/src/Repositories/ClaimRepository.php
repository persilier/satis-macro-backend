<?php

namespace Satis2020\ServicePackage\Repositories;

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
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllClaims()
    {
        return $this->claim->newQuery()->get();
    }

    /***
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllClaimsWithRelations()
    {
        return $this->claim->newQuery()->with(Constants::getClaimRelations())->get();
    }

    public function getClaimsByCategory($institutionId=null)
    {

    }
}