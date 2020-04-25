<?php

namespace Satis2020\ClaimObjectPackage\Http\Controllers\ClaimCategory;

use Satis2020\ServicePackage\Models\ClaimCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClaimCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(ClaimCategory::all(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required'
        ];

        $this->validate($request, $rules);

        $claimCategory = ClaimCategory::create($request->only(['name', 'description', 'others']));

        return response()->json($claimCategory, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\ClaimCategory  $claimCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ClaimCategory $claimCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Satis2020\ServicePackage\Models\ClaimCategory  $claimCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ClaimCategory $claimCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Satis2020\ServicePackage\Models\ClaimCategory  $claimCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClaimCategory $claimCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Satis2020\ServicePackage\Models\ClaimCategory  $claimCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClaimCategory $claimCategory)
    {
        //
    }
}
