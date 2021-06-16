<?php

namespace Satis2020\ClientPackage\Http\Controllers\TypeClients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ClientPackage\Http\Resources\TypeClient as TypeClientResource;
use Satis2020\ClientPackage\Http\Resources\TypeClientCollection;
class TypeClientController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-list-typeclient')->only(['index']);
        $this->middleware('permission:can-create-typeclient')->only(['store']);
        $this->middleware('permission:can-show-typeclient')->only(['show']);
        $this->middleware('permission:can-update-typeclient')->only(['update']);
        $this->middleware('permission:can-delete-typeclient')->only(['destroy']);*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return TypeClientCollection
     */

    public function index()
    {
        return new TypeClientCollection(TypeClient::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return TypeClientResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
            'institutions_id' => 'required|exists:institutions,id'
        ];
        $this->validate($request, $rules);
        if($type_exist = TypeClient::where('name->'.App::getLocale(),$request->name)->where('institutions_id',$request->institutions_id)->first())
            return $this->errorResponse('Ce type client-from-my-institution existe déjà dans votre institution', 421);

        $type_client = TypeClient::create(['name' => $request->name, 'description'=>$request->description, 'institutions_id'=>$request->institutions_id]);
        return new TypeClientResource($type_client);
    }

    /**
     * Display the specified resource.
     *
     * @param TypeClient $type_client
     * @return TypeClientResource
     */
    public function show(TypeClient $type_client)
    {
        return new TypeClientResource($type_client);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param TypeClient $type_client
     * @return TypeClientResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, TypeClient $type_client)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
            'institutions_id' => 'required|exists:institutions,id'
        ];
        $this->validate($request, $rules);
        if($type_exist = TypeClient::where('name->'.App::getLocale(),$request->name)->where('institutions_id',$request->institutions_id)->first())
            return $this->errorResponse('Ce type client-from-my-institution existe déjà dans votre institution', 421);
        $type_client->update(['name'=> $request->name, 'description'=> $request->description, 'institutions_id'=>$request->institutions_id]);
        return new TypeClientResource($type_client);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TypeClient $type_client
     * @return TypeClientResource
     * @throws \Exception
     */
    public function destroy(TypeClient $type_client)
    {
        $type_client->delete();
        return new TypeClientResource($type_client);
    }
}
