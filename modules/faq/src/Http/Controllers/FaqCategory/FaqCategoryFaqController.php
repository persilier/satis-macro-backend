<?php

namespace Satis2020\Faq\Http\Controllers\FaqCategory;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\FaqCategory;

/**
 * Class FaqCategoryFaqController
 * @package Satis2020\FaqPackage\Http\Controllers\FaqCategory
 */
class FaqCategoryFaqController extends ApiController
{
    /**
     * UserPermissionController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
    }


    /**
     * @param FaqCategory $faqCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(FaqCategory $faqCategory)
    {
        $faqCategory->load('faqs');
        return response()->json($faqCategory->faqs, 201);
    }

}
