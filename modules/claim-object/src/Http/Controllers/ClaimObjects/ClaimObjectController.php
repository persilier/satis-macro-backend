<?php

namespace Satis2020\ClaimObject\Http\Controllers\ClaimObjects;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\SeverityLevel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

/**
 * Class ClaimObjectController
 * @package Satis2020\ClaimObject\Http\Controllers\ClaimObjects
 */
class ClaimObjectController extends ApiController
{
    use \Satis2020\ServicePackage\Traits\ClaimObject;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-claim-object')->only(['index']);
        $this->middleware('permission:store-claim-object')->only(['store']);
        $this->middleware('permission:update-claim-object')->only(['update']);
        $this->middleware('permission:show-claim-object')->only(['show']);
        $this->middleware('permission:destroy-claim-object')->only(['destroy']);
     }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(ClaimObject::with('claimCategory', 'severityLevel')->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return response()->json([
            'claimCategories' => ClaimCategory::all(),
            'severityLevels' => SeverityLevel::all()
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws ValidationException
     */
    public function store(Request $request)
    {

        $this->validate($request, $this->rules($request));
        $claimObject = ClaimObject::create($request->only([
            'name', 
            'description', 
            'claim_category_id', 
            'severity_levels_id', 
            'time_limit', 
            'others',
            'time_unit',
            'time_staff',
            'time_treatment',
            'time_validation',
            'time_measure_satisfaction',
            'internal_control'
           
        ]));
        return response()->json($claimObject, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param ClaimObject $claimObject
     * @return JsonResponse
     */
    public function show(ClaimObject $claimObject)
    {
        return response()->json($claimObject->load('claimCategory', 'severityLevel'), 200);
    }

    /**
     * Edit the form for creating a new resource.
     * @param ClaimObject $claimObject
     * @return Response
     */
    public function edit(ClaimObject $claimObject)
    {
        return response()->json([
            'claimObject' => $claimObject->load('claimCategory', 'severityLevel'),
            'claimCategories' => ClaimCategory::all(),
            'severityLevels' => SeverityLevel::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param ClaimObject $claimObject
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, ClaimObject $claimObject)
    {
        $this->validate($request, $this->rules($request, $claimObject));
        $claimObject->update($request->only([
            'name', 
            'description', 
            'claim_category_id', 
            'severity_levels_id', 
            'time_limit', 
            'others',
            'time_unit',
            'time_staff',
            'time_treatment',
            'time_validation',
            'time_measure_satisfaction',
            'internal_control'
        ]));
        return response()->json($claimObject, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ClaimObject $claimObject
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(ClaimObject $claimObject)
    {
        $claimObject->secureDelete('claims');
        return response()->json($claimObject, 201);
    }

    /**
     * quota delay Calculation
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws ValidationException
     */
    public function quotaDelayCalculation(Request $request)
    {

        $rules = [
            'total_days' => 'required|numeric',
        ];

        $this->validate($request, $rules);

        $parameters = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'configuration-quota-delay')->first()->data));

        $data = [];

        foreach ($parameters as $key =>  $value) {

            $total_days = (intval($value) * $request->total_days) / 100;

            if ($total_days < 1) {
                $hours = $total_days * 24;
                $time_limit = $hours."h";
            } else {
                
                $whole = floor($total_days);
                $decimal = fmod($total_days, $whole);
                $hours = $decimal * 24;
                $time_limit = $whole."j"." ".$hours."h";
            }

            $data += [ $key => $time_limit ];
           
        }
        return response()->json($data, 200);
    }
}
