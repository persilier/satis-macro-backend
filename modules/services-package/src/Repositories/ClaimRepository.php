<?php


namespace Satis2020\ServicePackage\Repositories;


use Satis2020\ServicePackage\Models\Claim;

class ClaimRepository
{

    public function __construct()
    {
        $this->claim = new Claim();
    }

    public function getAllClaims($institutionId=null)
    {

    }

    public function getClaimsByCategory($institutionId=null)
    {

    }
}