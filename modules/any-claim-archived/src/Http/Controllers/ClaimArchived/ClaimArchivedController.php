<?php

namespace Satis2020\AnyClaimArchived\Http\Controllers\ClaimArchived;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\ClaimSatisfactionMeasured;


/** 
 * Class ClaimArchivedController
 * @package Satis2020\AnyClaimArchived\Http\Controllers\ClaimArchived
 */
class ClaimArchivedController extends ApiController
{
    use ClaimSatisfactionMeasured;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-any-claim-archived')->only(['index']);
        $this->middleware('permission:show-any-claim-archived')->only(['show']);
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        $paginationSize = \request()->query('size');
        $recherche = \request()->query('key');
        $institutionId = \request()->query('institution_id');

        $claims = $this->getArchivedMyClaim('archived', true, $paginationSize, $recherche, 'status', $institutionId);

        return response()->json($claims, 200);
    }


    /**
     * @param $claim
     * @return JsonResponse
     */
    public function show($claim)
    {
        $claim = $this->getClaim('archived')->findOrFail($claim);
        return response()->json($claim, 200);
    }
}
