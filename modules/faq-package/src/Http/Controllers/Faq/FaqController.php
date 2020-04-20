<?php

namespace Satis2020\FaqPackage\Http\Controllers\Category;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Faq;

class FaqController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        /*$this->middleware('permission:can-list-faqcategory')->only(['index']);
        $this->middleware('permission:can-create-faqcategory')->only(['store']);
        $this->middleware('permission:can-show-faqcategory')->only(['show']);
        $this->middleware('permission:can-update-faqcategory')->only(['update']);
        $this->middleware('permission:can-delete-faqcategory')->only(['destroy']);*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return PermissionCollection
     */
    public function index()
    {
        $faq_categories = FaqCategory::all();
        dd($faq_categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return FaqCategoryResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:faq_categories',
            'contenu'=>'required|'
        ];
        $this->validate($request, $rules);
        $faq_category = FaqCategory::create(['name' => $request->name,'slug'=> $request->name, 'content' => $request->contenu]);
        dd($faq_category);
        //return new FaqCategoryResource($faq_category);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $name
     * @return FaqCategoryResource
     */
    public function show($name)
    {
        return new FaqCategoryResource(
            FaqCategory::where('name', $name)->firstOrFail()
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $permission
     * @return FaqCategoryResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $name)
    {
        $rules = [
            'name' => 'required|exists:'.config('permission.table_names.permissions'),
        ];
        $this->validate($request, $rules);
        $faq_category = FaqCategory::where('name', $name)->firstOrFail();
        $faq_category->name = $request->name;
        $faq_category->content = $request->contenu;
        if(!$permission->isDirty()){
            return $this->errorResponse('Vous devez spécifier une valeur différente à mettre à jour', 422);
        }
        $faq_category->save();
        return new FaqCategoryResource($faq_category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $permission
     * @return PermissionResource
     */
    public function destroy($name)
    {
        $name = FaqCategory::where('name', $name)->firstOrFail();
        $name->delete();
        return new FaqCategoryResource($name);
    }
}
