<?php

namespace Satis2020\FaqPackage\Http\Controllers\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Faq;
use Satis2020\FaqPackage\Http\Resources\Faq as FaqResource;
use Satis2020\FaqPackage\Http\Resources\FaqCollection;
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
     * @return FaqCollection
     */
    /**
     * Display a listing of the resource.
     *
     * @return FaqCollection
     */
    public function index()
    {
        return new FaqCollection(Faq::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return FaqResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'question' => 'required|string',
            'answer' => 'required|string',
            'faq_category_id' => 'required|exists:faq_categories,id'
        ];
        $this->validate($request, $rules);
        if($faq = Faq::where('question->'.App::getLocale(), $request->question)->where('faq_category_id',$request->faq_category_id)->first())
            return $this->errorResponse('Cette question de faq existe déjà pour cette catégorie.', 400);
        $faq = Faq::create(['question' => $request->question, 'answer'=>$request->answer, 'faq_category_id'=>$request->faq_category_id]);
        return new FaqResource($faq);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return FaqResource
     */
    public function show($slug)
    {
        $faq = Faq::where('slug->'.App::getLocale(), $slug)->orWhere('id',$slug)->firstOrFail();
        return new FaqResource($faq);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @return FaqResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $slug)
    {
        $rules = [
            'question' => 'required|string',
            'answer' => 'required|string',
            'faq_category_id' => 'required|exists:faq_categories,id'
        ];
        $this->validate($request, $rules);

        $faq = Faq::where('slug->'.App::getLocale(), $slug)
            ->orWhere('id',$slug)->firstOrFail();
        if($check = Faq::where('question->'.App::getLocale(), $request->question)->where('faq_category_id','!=',$faq->faq_category_id)->first())
            return $this->errorResponse('Veuillez renseigner une autre question, car celle ci existe déjà dans cette catégorie.', 400);

        $faq->slug = null;
        $faq->update(['question'=> $request->question, 'answer'=> $request->answer, 'faq_category_id'=> $request->faq_category_id]);
        return new FaqResource($faq);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $slug
     * @return FaqResource
     */
    public function destroy($slug)
    {
        $faq = Faq::where('slug->'.App::getLocale(), $slug)
            ->orWhere('id',$slug)->firstOrFail();
        $faq->delete();
        return new FaqResource($faq);
    }
}
