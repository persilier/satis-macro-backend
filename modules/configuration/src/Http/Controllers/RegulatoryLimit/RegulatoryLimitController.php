<?php

namespace Satis2020\Configuration\Http\Controllers\RegulatoryLimit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

/**
 * Class RelanceController
 * @package Satis2020\Configuration\Http\Controllers\Relance
 */
class RegulatoryLimitController extends ApiController
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
     * @return JsonResponse
     */
    public function show()
    {
        return response()->json(["limit" => json_decode(Metadata::query()->where('name', Metadata::REGULATORY_LIMIT)->firstOrFail()->data)]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'limit' => 'required|integer',
        ];

        $this->validate($request, $rules);

        Metadata::query()
            ->where('name', Metadata::REGULATORY_LIMIT)
            ->firstOrFail()
            ->update(['data' => json_encode($request->limit)]);

        return response()->json($request->only('limit'), 200);
    }

}
