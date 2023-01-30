<?php


namespace Satis2020\ServicePackage\Http\Controllers\Claim;



use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\ClaimTrait;

class ClaimController extends ApiController
{
    use ClaimTrait;

    /**
     * StateController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function show($claim_id)
    {
        return response($this->getOneClaimQuery($claim_id));
    }

}