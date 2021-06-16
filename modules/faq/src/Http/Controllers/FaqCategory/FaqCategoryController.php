<?php

namespace Satis2020\Faq\Http\Controllers\FaqCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\FaqCategory;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

/**
 * Class FaqCategoryController
 * @package Satis2020\Faq\Http\Controllers\FaqCategory
 */
class FaqCategoryController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-faq-category')->only(['index']);
        $this->middleware('permission:store-faq-category')->only(['store']);
        $this->middleware('permission:show-faq-category')->only(['show']);
        $this->middleware('permission:update-faq-category')->only(['update']);
        $this->middleware('permission:destroy-faq-category')->only(['destroy']);
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(FaqCategory::all(), 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('faq_categories', 'name')],
        ];

        $this->validate($request, $rules);

        $faqCategory = FaqCategory::create($request->only('name'));

        return response()->json($faqCategory, 201);
    }


    /**
     * @param FaqCategory $faqCategory
     * @return JsonResponse
     */
    public function show(FaqCategory $faqCategory)
    {
        return response()->json($faqCategory, 200);
    }


    /**
     * @param Request $request
     * @param FaqCategory $faqCategory
     * @return JsonResponse
     * @throws ValidationException
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
        return response()->json($faqCategory, 201);
    }


    /**
     * @param FaqCategory $faqCategory
     * @return JsonResponse
     */
    public function destroy(FaqCategory $faqCategory)
    {
        $faqCategory->secureDelete('faqs');
        return response()->json($faqCategory, 200);
    }
}
