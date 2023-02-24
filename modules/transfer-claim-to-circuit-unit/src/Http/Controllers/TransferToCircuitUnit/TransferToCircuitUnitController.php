<?php

namespace Satis2020\TransferClaimToCircuitUnit\Http\Controllers\TransferToCircuitUnit;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Rules\UnitBelongsToCircuitRules;
use Satis2020\ServicePackage\Rules\UnitBelongsToInstitutionRules;
use Satis2020\ServicePackage\Rules\UnitCanTreatRules;
use Satis2020\ServicePackage\Rules\UnitIsParentRules;
use Satis2020\ServicePackage\Traits\AwaitingAssignment;
use Satis2020\ServicePackage\Traits\HandleTreatment;
use Satis2020\ServicePackage\Traits\UnitsPrediction;

class TransferToCircuitUnitController extends ApiController
{

    use HandleTreatment, AwaitingAssignment, UnitsPrediction;

    public function __construct()
    {
       parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:transfer-claim-to-circuit-unit')->only(['update', 'edit']);

        $this->middleware('active.pilot')->only(['update', 'edit']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Claim $claim
     * @return \Illuminate\Http\Response
     */
    public function edit(Claim $claim)
    {
        $claim->load('claimObject.units.institution');

        return response()->json([
            'units' => $claim->claimObject->units->filter(function ($item, $key) {
                return $item->institution->id == $this->institution()->id;
            })
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, Claim $claim)
    {
        $rules = [
            'unit_id' => [
                'required', 'exists:units,id', new UnitBelongsToInstitutionRules($this->institution()->id),
                new UnitCanTreatRules, new UnitBelongsToCircuitRules($claim->id),new UnitIsParentRules($claim->id)
            ],
        ];

        $this->validate($request, $rules);

        $claim = $this->transferToUnit($request, $claim);

        return response()->json($claim, 201);
    }

    // public function getUnitsPrediction($description,$object){
    //    return response($this->allUnitsPrediction($description,$object));
    // }
    public function getUnitsPrediction(Request $request){
       return response($this->allUnitsPrediction($request->description,$request->object));
    }

}
