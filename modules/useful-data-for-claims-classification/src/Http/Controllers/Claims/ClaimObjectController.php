<?php

namespace Satis2020\UsefulDataForClaimsClassification\Http\Controllers\Claims;

use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Services\ClaimClassificationService;
use Satis2020\ServicePackage\Traits\ClaimTrait;

class ClaimObjectController extends Controller
{

    use ClaimTrait;

    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('client.credentials');
    }


    public function index(ClaimClassificationService $claimClassificationService, $claimCategoryName)
    {
        return response()->json($claimClassificationService->getTimeLimitByObjectName($claimCategoryName), 200);
    }

}
