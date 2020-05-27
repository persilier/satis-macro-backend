<?php

namespace Satis2020\CategoryClient\Http\Controllers\CategoryClients;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\CategoryClient;
class CategoryClientController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-category-client')->only(['index']);
        $this->middleware('permission:store-category-client')->only(['store']);
        $this->middleware('permission:show-category-client')->only(['show']);
        $this->middleware('permission:update-category-client')->only(['update']);
        $this->middleware('permission:delete-category-client')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return response()->json(CategoryClient::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
        ];
        $this->validate($request, $rules);
        $categoryClient = CategoryClient::create($request->only(['name', 'description']));
        return response()->json($categoryClient, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param CategoryClient $categoryClient
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(CategoryClient $categoryClient)
    {
        return response()->json($categoryClient, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param CategoryClient $categoryClient
     * @return \Illuminate\Http\JsonResponse|TypeClientResource
     * @throws ValidationException
     */
    public function update(Request $request, CategoryClient $categoryClient)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
        ];
        $this->validate($request, $rules);
        $categoryClient->update(['name'=> $request->name, 'description'=> $request->description]);
        return response()->json($categoryClient, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CategoryClient $categoryClient
     * @return TypeClientResource
     * @throws \Satis2020\ServicePackage\Exceptions\SecureDeleteException
     */
    public function destroy(CategoryClient $categoryClient)
    {
        $categoryClient->secureDelete('clients');
        return response()->json($categoryClient, 200);
    }
}
