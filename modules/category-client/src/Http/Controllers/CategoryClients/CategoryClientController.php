<?php

namespace Satis2020\CategoryClient\Http\Controllers\CategoryClients;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Traits\SecureDelete;
class CategoryClientController extends ApiController
{
    use SecureDelete;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-category-client-from-my-institution')->only(['index']);
        $this->middleware('permission:store-category-client-from-my-institution')->only(['store']);
        $this->middleware('permission:show-category-client-from-my-institution')->only(['show']);
        $this->middleware('permission:update-category-client-from-my-institution')->only(['update']);
        $this->middleware('permission:destroy-category-client-from-my-institution')->only(['destroy']);
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
        $category_client = CategoryClient::create($request->only(['name', 'description']));
        return response()->json($category_client, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param CategoryClient $category_client
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(CategoryClient $category_client)
    {
        return response()->json($category_client, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param CategoryClient $category_client
     * @return \Illuminate\Http\JsonResponse|TypeClientResource
     * @throws ValidationException
     */
    public function update(Request $request, CategoryClient $category_client)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
        ];
        $this->validate($request, $rules);
        $category_client->update(['name'=> $request->name, 'description'=> $request->description]);
        return response()->json($category_client, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CategoryClient $category_client
     * @return TypeClientResource
     * @throws \Satis2020\ServicePackage\Exceptions\SecureDeleteException
     */
    public function destroy(CategoryClient $category_client)
    {
        $category_client->secureDelete('clients');
        return response()->json($category_client, 200);
    }
}
