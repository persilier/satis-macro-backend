<?php

namespace Satis2020\Configuration\Http\Controllers\MeasurePreventive;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

/**
 * Class MeasurePreventiveController
 * @package Satis2020\Configuration\Http\Controllers\MeasurePreventive
 */
class MeasurePreventiveController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-measure-preventive-parameters')->only(['show','update']);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return response()->json(["measure-preventive" => json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'measure-preventive')->firstOrFail()->data)], 200);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'measure_preventive' => ['required', Rule::in([true, false])],
        ];

        $this->validate($request, $rules);

        Metadata::where('name', 'measure-preventive')->firstOrFail()->update(['data' => json_encode($request->measure_preventive)]);

        return response()->json($request->only('measure_preventive'), 200);
    }

}
