<?php

namespace Satis2020\FaqPackage\Http\Controllers\FaqCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\FaqCategory;
use Satis2020\FaqPackage\Http\Resources\FaqCategory as FaqCategoryResource;
use Satis2020\FaqPackage\Http\Resources\FaqCategoryCollection;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

class FaqCategoryController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('permission:list-faq-category')->only(['index']);
        $this->middleware('permission:store-faq-category')->only(['store']);
        $this->middleware('permission:show-faq-category')->only(['show']);
        $this->middleware('permission:update-faq-category')->only(['update']);
        $this->middleware('permission:destroy-faq-category')->only(['destroy']);
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
            'name' => ['required', new TranslatableFieldUnicityRules('faq_categories', 'name')],
        ];
        $this->validate($request, $rules);

        $faqCategory = FaqCategory::create(['name' => $request->name]);
        return new FaqCategoryResource($faqCategory);
    }

    /**
     * Display the specified resource.
     *
     * @param FaqCategory $faqCategory
     * @return FaqCategoryResource
     */
    public function show(FaqCategory $faqCategory)
    {
        return new FaqCategoryResource(
            $faqCategory
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $faqCategory
     * @return FaqCategoryResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, FaqCategory $faqCategory)
    {
        
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('faq_categories', 'name', 'id', "{$faqCategory->id}")],
        ];
        $this->validate($request, $rules);

        $faqCategory->slug = null;
        $faqCategory->name = $request->name;
        $faqCategory->save();
        return new FaqCategoryResource($faqCategory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param FaqCategory $faqCategory
     * @return FaqCategoryResource
     * @throws \Exception
     */
    public function destroy(FaqCategory $faqCategory)
    {
        $faqCategory->secureDelete('faqs');
        return new FaqCategoryResource($faqCategory);
    }
}
