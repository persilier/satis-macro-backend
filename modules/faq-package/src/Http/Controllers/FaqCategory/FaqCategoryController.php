<?php

namespace Satis2020\FaqPackage\Http\Controllers\FaqCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\FaqCategory;
use Satis2020\FaqPackage\Http\Resources\FaqCategory as FaqCategoryResource;
use Satis2020\FaqPackage\Http\Resources\FaqCategoryCollection;

class FaqCategoryController extends ApiController
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
     * @return FaqCategoryCollection
     */
    public function index()
    {
        return new FaqCategoryCollection(FaqCategory::all());
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
            'name' => 'required|string|max:255'
        ];
        $this->validate($request, $rules);
        if($category = FaqCategory::where('name->'.App::getLocale(), $request->name)->first())
            return $this->errorResponse('Cette catégorie de faq existe déjà dans la base.', 400);
        $faq_category = FaqCategory::create(['name' => $request->name]);
        return new FaqCategoryResource($faq_category);
    }

    /**
     * Display the specified resource.
     *
     * @param FaqCategory $faq_category
     * @return FaqCategoryResource
     */
    public function show($faq_category)
    {
        $category = FaqCategory::where('slug->'.App::getLocale(), $faq_category)->orWhere('id',$faq_category)->firstOrFail();
        return new FaqCategoryResource(
            $category
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $faq_category
     * @return FaqCategoryResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $faq_category)
    {
        $rules = [
            'name' => 'required|string|max:255'
        ];
        $this->validate($request, $rules);

        $category = FaqCategory::where('slug->'.App::getLocale(), $faq_category)
                                    ->orWhere('id',$faq_category)->firstOrFail();

        if($check = FaqCategory::where('name->'.App::getLocale(), $request->name)->first())
            return $this->errorResponse('Cette catégorie de faq existe déjà dans la base.', 400);
        $category->slug = null;
        $category->name = $request->name;
        $category->save();
        return new FaqCategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param FaqCategory $faq_category
     * @return FaqCategoryResource
     * @throws \Exception
     */
    public function destroy($faq_category)
    {
        $category = FaqCategory::where('slug->'.App::getLocale(), $faq_category)
            ->orWhere('id',$faq_category)->firstOrFail();
        $category->secureDelete('faqs');
        return new FaqCategoryResource($category);
    }
}
