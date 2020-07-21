<?php

namespace Satis2020\ClaimSatisfactionMeasured\Http\Controllers\ClaimArchived;
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
 * @package Satis2020\ClaimSatisfactionMeasured\Http\Controllers\ClaimArchived
 */
class ClaimArchivedController extends ApiController
{
    use ClaimSatisfactionMeasured;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-archived')->only(['index']);
        $this->middleware('permission:show-claim-archived')->only(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function index()
    {
        $institution = $this->institution();
        $claims = $this->getClaim($institution->id, 'archived')->get();
        return response()->json($claims, 200);
    }


    /**
     * @param $claim
     * @return JsonResponse
     */
    public function show($claim)
    {
        $institution = $this->institution();
        $claim = $this->getClaim($institution->id, 'archived')->findOrFail($claim);
        return response()->json($claim, 200);
    }



}


