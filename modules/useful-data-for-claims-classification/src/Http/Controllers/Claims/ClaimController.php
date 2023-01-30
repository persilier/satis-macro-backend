<?php

namespace Satis2020\UsefulDataForClaimsClassification\Http\Controllers\Claims;

use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Services\ClaimClassificationService;
use Satis2020\ServicePackage\Traits\ClaimTrait;

class ClaimController extends Controller
{

    use ClaimTrait;

    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('client.credentials');
    }


    public function index(ClaimClassificationService $claimClassificationService)
    {
        return response()->json($claimClassificationService->getAllClaimClassification(), 200);
    }

}
