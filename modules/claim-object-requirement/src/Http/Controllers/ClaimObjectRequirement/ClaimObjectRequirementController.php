<?php

namespace Satis2020\ClaimObjectRequirement\Http\Controllers\ClaimObjectRequirement;

use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Requirement;

class ClaimObjectRequirementController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:update-claim-object-requirement')->only(['update', 'edit']);
    }

    /**
     * Edit the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        return response()->json([
            'claimCategories' => ClaimCategory::with('claimObjects.requirements')->get(),
            'requirements' => Requirement::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws RetrieveDataUserNatureException
     */
    public function update(Request $request)
    {
        $collection = collect([]);

        foreach ($request->all() as $claim_object_id => $requirement_ids) {
            // Check if claim_object_id exists
            $claim_object = ClaimObject::findOrFail($claim_object_id);

            // Check if requirement_ids don't contain same values and exist
            $requirement_ids_collection = collect([]);

            if(!is_null($requirement_ids)){

                foreach ($requirement_ids as $requirement_id) {

                    if ($requirement_ids_collection->search($requirement_id, true) !== false) {
                        throw new RetrieveDataUserNatureException($requirement_id . " is sent more than once");
                    }

                    Requirement::findOrFail($requirement_id);

                    $requirement_ids_collection->push($requirement_id);
                }

            }else{

                $requirement_ids = [];
            }

            $collection->push([
                'claim_object' => $claim_object,
                'requirement_ids' => $requirement_ids
            ]);
        }

        $collection->each(function ($item, $key) {
            $item['claim_object']->requirements()->sync($item['requirement_ids']);
        });

        return response()->json([
            'claimCategories' => ClaimCategory::with('claimObjects.requirements')->get(),
            'requirements' => Requirement::all()
        ], 201);

    }


}
