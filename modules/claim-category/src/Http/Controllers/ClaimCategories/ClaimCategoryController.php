<?php

namespace Satis2020\ClaimCategory\Http\Controllers\ClaimCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\SecureDeleteException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\ClaimCategory;

/**
 * Class ClaimCategoryController
 * @package Satis2020\ClaimCategory\Http\Controllers\ClaimCategories
 */
class ClaimCategoryController extends ApiController
{
    use \Satis2020\ServicePackage\Traits\ClaimCategory;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-claim-category')->only(['index']);
        $this->middleware('permission:store-claim-category')->only(['store']);
        $this->middleware('permission:update-claim-category')->only(['update']);
        $this->middleware('permission:show-claim-category')->only(['show']);
        $this->middleware('permission:destroy-claim-category')->only(['destroy']);
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(ClaimCategory::all(), 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules());
        $claimCategory = ClaimCategory::create($request->only(['name', 'description', 'others']));
        return response()->json($claimCategory, 201);
    }


    /**
     * @param ClaimCategory $claimCategory
     * @return JsonResponse
     */
    public function show(ClaimCategory $claimCategory)
    {
        return response()->json($claimCategory, 200);
    }


    /**
     * @param Request $request
     * @param ClaimCategory $claimCategory
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, ClaimCategory $claimCategory)
    {

        $this->validate($request, $this->rules($claimCategory));
        $claimCategory->update($request->only(['name', 'description', 'others']));
        return response()->json($claimCategory, 201);

    }


    /**
     * @param ClaimCategory $claimCategory
     * @return JsonResponse
     */
    public function destroy(ClaimCategory $claimCategory)
    {
        $claimCategory->secureDelete('claimObjects');
        return response()->json($claimCategory, 201);
    }
}
