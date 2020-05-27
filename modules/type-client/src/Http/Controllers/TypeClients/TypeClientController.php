<?php

namespace Satis2020\TypeClient\Http\Controllers\TypeClients;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\TypeClient;
class TypeClientController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-type-client')->only(['index']);
        $this->middleware('permission:store-type-client')->only(['store']);
        $this->middleware('permission:show-type-client')->only(['show']);
        $this->middleware('permission:update-type-client')->only(['update']);
        $this->middleware('permission:delete-type-client')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return response()->json(TypeClient::all(), 200);
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
        $clientType = TypeClient::create($request->only(['name', 'description']));
        return response()->json($clientType, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param TypeClient $clientClient
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(TypeClient $clientClient)
    {
        return response()->json($clientClient, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param TypeClient $clientClient
     * @return \Illuminate\Http\JsonResponse|TypeClientResource
     * @throws ValidationException
     */
    public function update(Request $request, TypeClient $clientClient)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
        ];
        $this->validate($request, $rules);
        $clientClient->update(['name'=> $request->name, 'description'=> $request->description]);
        return response()->json($clientClient, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TypeClient $clientClient
     * @return TypeClientResource
     * @throws \Satis2020\ServicePackage\Exceptions\SecureDeleteException
     */
    public function destroy(TypeClient $clientClient)
    {
        $clientClient->secureDelete('clients');
        return response()->json($clientClient, 200);
    }
}
