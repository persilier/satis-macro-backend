<?php

namespace Satis2020\ClaimCategory\Http\Controllers\ClaimCategories;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\ClaimCategory;

class ClaimCategoryObjectController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     * @param ClaimCategory $claimCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ClaimCategory $claimCategory)
    {
        $claimCategory->load('claimObjects');
        return response()->json($claimCategory->only(['claimObjects']), 200);
    }

}
