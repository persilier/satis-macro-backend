<?php

namespace Satis2020\Configuration\Http\Controllers\Relance;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

/**
 * Class RelanceController
 * @package Satis2020\Configuration\Http\Controllers\Relance
 */
class RelanceController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-relance-parameters')->only(['show','update']);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $coef = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'coef-relance')->first()->data);
        return response()->json(['coef' => $coef], 200);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {

        $coef = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'coef-relance')->first()->data);

        $rules = [
            'coef' => 'required|integer',
        ];

        $this->validate($request, $rules);

        Metadata::where('name', 'coef-relance')->first()->update(['data'=> $request->coef]);

        return response()->json(['coef' => $request->coef], 200);
    }

}