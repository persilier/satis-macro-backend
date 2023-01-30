<?php

namespace Satis2020\Faq\Http\Controllers\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Faq;
use Satis2020\ServicePackage\Models\FaqCategory;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

/**
 * Class FaqController
 * @package Satis2020\Faq\Http\Controllers\Faq
 */
class FaqController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        //$this->middleware('permission:list-faq')->only(['index']);
        $this->middleware('permission:store-faq')->only(['store']);
        $this->middleware('permission:show-faq')->only(['show']);
        $this->middleware('permission:update-faq')->only(['update']);
        $this->middleware('permission:delete-faq')->only(['destroy']);
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Faq::with('faqCategory')->get(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'question' => ['required','string', new TranslatableFieldUnicityRules('faqs', 'question')],
            'answer' => 'required|string',
            'faq_category_id' => 'required|exists:faq_categories,id'
        ];
        $this->validate($request, $rules);

        $faq = Faq::create($request->only('question', 'answer', 'faq_category_id'));

        return response()->json($faq, 201);
    }


    /**
     * @param Faq $faq
     * @return JsonResponse
     */
    public function show(Faq $faq)
    {
        return response()->json($faq->load('faqCategory'), 200);
    }


    /**
     * @return JsonResponse
     */
    public function categoryAll(){

        return response()->json(FaqCategory::all(), 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $faq
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request,Faq $faq)
    {

        $rules = [
            'question' => ['required','string', new TranslatableFieldUnicityRules('faqs', 'question', 'id', "{$faq->id}")],
            'answer' => 'required|string',
            'faq_category_id' => 'required|exists:faq_categories,id'
        ];
        $this->validate($request, $rules);

        $faq->slug = null;
        $faq->update(['question'=> $request->question, 'answer'=> $request->answer, 'faq_category_id'=> $request->faq_category_id]);
        return response()->json($faq, 200);
    }


    /**
     * @param Faq $faq
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json($faq, 200);
    }
}
