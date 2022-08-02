<?php

namespace Satis2020\Relationship\Http\Controllers\Relationship;

use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Relationship;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;
use Symfony\Component\HttpFoundation\JsonResponse;


class RelationshipController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->middleware('auth:api');
        
        $this->middleware('permission:list-relationship')->only(['index']);
        $this->middleware('permission:store-relationship')->only(['store']);
        $this->middleware('permission:update-relationship')->only(['update']);
        $this->middleware('permission:show-relationship')->only(['show']);
        $this->middleware('permission:destroy-relationship')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return response()->json(Relationship::all(), 200);
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
            'name' => ['required', new TranslatableFieldUnicityRules('relationships', 'name')],
            'description' => 'nullable',
        ];
        $this->validate($request, $rules);
        $relationship = Relationship::create($request->only(['name', 'description']));
        return response()->json($relationship, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param Relationship $relationship
     * @return JsonResponse
     */
    public function show(Relationship $relationship)
    {
        return response()->json($relationship, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Relationship $relationship
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Relationship $relationship)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('relationships', 'name', 'id', "{$relationship->id}")],
            'description' => 'nullable',
        ];
        $this->validate($request, $rules);
        $relationship->update($request->only(['name', 'description']));
        return response()->json($relationship, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Relationship $relationship
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Relationship $relationship)
    {
        $relationship->delete();
        return response()->json($relationship, 200);
    }
}
