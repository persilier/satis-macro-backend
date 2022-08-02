<?php

namespace Satis2020\SearchClaimAnyReference\Http\Controllers\Search;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\SearchClaimReference;

/**
 * Class SearchController
 * @package Satis2020\SearchClaimReference\Http\Controllers\Search
 */
class SearchController extends ApiController
{
    use DataUserNature, SearchClaimReference;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:search-claim-any-reference')->only(['index']);
    }


    /**
     * @param $reference
     * @return JsonResponse
     */
    public function index($reference)
    {
        return response()->json($this->searchClaim($reference), 200);
    }

}
