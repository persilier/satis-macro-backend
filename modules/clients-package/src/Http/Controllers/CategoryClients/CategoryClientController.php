<?php

namespace Satis2020\ClientPackage\Http\Controllers\CategoryClients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ClientPackage\Http\Resources\CategoryClient as CategoryClientResource;
use Satis2020\ClientPackage\Http\Resources\CategoryClientCollection;
class CategoryClientController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-list-categoryclient')->only(['index']);
        $this->middleware('permission:can-create-categoryclient')->only(['store']);
        $this->middleware('permission:can-show-categoryclient')->only(['show']);
        $this->middleware('permission:can-update-categoryclient')->only(['update']);
        $this->middleware('permission:can-delete-categoryclient')->only(['destroy']);*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return CategoryClientCollection
     */

    public function index()
    {
        return new CategoryClientCollection(CategoryClient::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return CategoryClientResource
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
        if($category_exist = CategoryClient::where('name->'.App::getLocale(),$request->name)->where('institutions_id',$request->institutions_id)->first())
            return $this->errorResponse('Cette catégorie client-from-my-institution existe déjà dans votre institution', 421);
        $category_client = CategoryClient::create(['name' => $request->name, 'description'=>$request->description, 'institutions_id'=>$request->institutions_id]);
        return new CategoryClientResource($category_client);
    }

    /**
     * Display the specified resource.
     *
     * @param CategoryClient $category_client
     * @return CategoryClientResource
     */
    public function show(CategoryClient $category_client)
    {
        return new CategoryClientResource($category_client);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param TypeClient $type_client
     * @return CategoryClientResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, CategoryClient $category_client)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
            'institutions_id' => 'required|exists:institutions,id'
        ];
        $this->validate($request, $rules);
        if($category_exist = CategoryClient::where('name->'.App::getLocale(),$request->name)->where('institutions_id',$request->institutions_id)->first())
            return $this->errorResponse('Veuillez renseigner un autre nom de catégorie. Celle-ci existe déjà dans votre institution.', 421);

        $category_client->update(['name'=> $request->name, 'description'=> $request->description, 'institutions_id'=>$request->institutions_id]);
        return new CategoryClientResource($category_client);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CategoryClient $category_client
     * @return CategoryClientResource
     * @throws \Exception
     */
    public function destroy(CategoryClient $category_client)
    {
        $category_client->delete();
        return new CategoryClientResource($category_client);
    }
}
