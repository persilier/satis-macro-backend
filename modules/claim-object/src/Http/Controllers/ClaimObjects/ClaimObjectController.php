<?php

namespace Satis2020\ClaimObject\Http\Controllers\ClaimObjects;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\ClaimObject;
class ClaimObjectController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-claim-object')->only(['index']);
        $this->middleware('permission:store-claim-object')->only(['store']);
        $this->middleware('permission:update-claim-object')->only(['update']);
        $this->middleware('permission:show-claim-object')->only(['show']);
        $this->middleware('permission:destroy-claim-object')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return response()->json(ClaimObject::with('claimCategory','severityLevel')->get(), 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
            'claim_category_id' => 'required|exists:claim_categories,id',
            'severity_levels_id' => 'exists:severity_levels,id',
            'time_limit' => 'required|integer',
            'others' => 'array',
        ];
        $this->validate($request, $rules);
        $claimObject = ClaimObject::create($request->only(['name', 'description','claim_category_id','severity_levels_id','time_limit' ,'others']));
        return response()->json($claimObject, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param ClaimObject $claimObject
     * @return JsonResponse
     */
    public function show(ClaimObject $claimObject)
    {
        return response()->json($claimObject->load('claimCategory','severityLevel'), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param ClaimObject $claimObject
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, ClaimObject $claimObject)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
            'claim_category_id' => 'required|exists:claim_categories,id',
            'severity_levels_id' => 'exists:severity_levels,id',
            'time_limit' => 'required|integer',
            'others' => 'array',
        ];
        $this->validate($request, $rules);
        $claimObject->update($request->only(['name', 'description','claim_category_id','severity_levels_id','time_limit' ,'others']));
        return response()->json($claimObject, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ClaimObject $claimObject
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(ClaimObject $claimObject)
    {
        $claimObject->delete();
        return response()->json($claimObject, 201);
    }
}
