<?php

namespace Satis2020\MetadataPackage\Http\Controllers\Plan;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

/**
 * Class PlanController
 * @package Satis2020\MetadataPackage\Http\Controllers\Plan
 */
class PlanController extends ApiController
{

    /**
     * ApiController constructor.
     */
    public function __construct()
    {

    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return response()->json([
            'plan' => Str::upper(json_decode(Metadata::ofName('app-nature')->firstOrFail()->data)),
            'year_installation' => env('APP_YEAR_INSTALLATION', '2021')
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'plan' => [
                'required',
                Rule::in(['pro', 'macro', 'hub']),
            ],
        ];

        $this->validate($request, $rules);

        Metadata::where('name', 'app-nature')->first()->update(['data' => json_encode($request->plan)]);

        // Prepare the specificity of the database link with the nature of the app

        return response()->json($request->plan, 201);
    }

}
