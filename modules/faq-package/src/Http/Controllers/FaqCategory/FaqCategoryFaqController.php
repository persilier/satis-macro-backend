<?php

namespace Satis2020\FaqPackage\Http\Controllers\FaqCategory;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Models\FaqCategory;
use Satis2020\FaqPackage\Http\Resources\FaqCollection;
class FaqCategoryFaqController extends ApiController
{
    /**
     * UserPermissionController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @param String $slug
     * @return FaqCollection
     */
    public function index($slug)
    {
        $category = FaqCategory::where('slug->'.App::getLocale(), $slug)->orWhere('id',$slug)->firstOrFail();
        return new FaqCollection($category->faqs);
    }

}
