<?php

namespace Satis2020\CategoryClient\Http\Controllers\CategoryClients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;
use Satis2020\ServicePackage\Traits\SecureDelete;

class CategoryClientController extends ApiController
{
    use SecureDelete;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-category-client')->only(['index']);
        $this->middleware('permission:store-category-client')->only(['store']);
        $this->middleware('permission:show-category-client')->only(['show']);
        $this->middleware('permission:update-category-client')->only(['update']);
        $this->middleware('permission:destroy-category-client')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */

    public function index()
    {
        return response()->json(CategoryClient::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('category_clients', 'name')],
            'description' => 'nullable',
        ];
        $this->validate($request, $rules);
        $category_client = CategoryClient::create($request->only(['name', 'description']));
        return response()->json($category_client, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param CategoryClient $category_client
     * @return JsonResponse
     */
    public function show(CategoryClient $category_client)
    {
        return response()->json($category_client, 200);
    }


    /**
     * @param Request $request
     * @param CategoryClient $category_client
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, CategoryClient $category_client)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('category_clients', 'name', 'id', "{$category_client->id}")],
            'description' => 'nullable',
        ];
        $this->validate($request, $rules);
        $category_client->update(['name'=> $request->name, 'description'=> $request->description]);
        return response()->json($category_client, 201);
    }


    /**
     * @param CategoryClient $category_client
     * @return JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\SecureDeleteException
     */
    public function destroy(CategoryClient $category_client)
    {
        $category_client->secureDelete('clients');
        return response()->json($category_client, 200);
    }
}
