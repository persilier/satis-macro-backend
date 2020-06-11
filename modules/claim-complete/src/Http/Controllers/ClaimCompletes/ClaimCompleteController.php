<?php

namespace Satis2020\ClaimComplete\Http\Controllers\ClaimComplete;
use Satis2020\ServicePackage\Exceptions\SecureDeleteException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Claim;
class ClaimCompleteController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-claim-complete')->only(['index']);
        $this->middleware('permission:store-claim-complete')->only(['store']);
        $this->middleware('permission:update-claim-complete')->only(['update']);
        $this->middleware('permission:show-claim-complete')->only(['show']);
        $this->middleware('permission:destroy-claim-complete')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return response()->json(Claim::all(), 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
            'others' => 'array',
        ];
        $this->validate($request, $rules);
        $claimCategory = ClaimCategory::create($request->only(['name', 'description', 'others']));
        return response()->json($claimCategory, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param ClaimCategory $claimCategory
     * @return JsonResponse
     */
    public function show(ClaimCategory $claimCategory)
    {
        return response()->json($claimCategory, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param ClaimCategory $claimCategory
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, ClaimCategory $claimCategory)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
            'others' => 'array',
        ];
        $this->validate($request, $rules);
        $claimCategory->update($request->only(['name', 'description', 'others']));
        return response()->json($claimCategory, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ClaimCategory $claimCategory
     * @return JsonResponse
     * @throws SecureDeleteException
     */
    public function destroy(ClaimCategory $claimCategory)
    {
        $claimCategory->secureDelete('claimObjects');
        return response()->json($claimCategory, 201);
    }
}
