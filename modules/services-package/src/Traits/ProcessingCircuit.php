<?php


namespace Satis2020\ServicePackage\Traits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Unit;

/**
 * Trait ProcessingCircuit
 * @package Satis2020\ServicePackage\Traits
 */
trait ProcessingCircuit
{

    /**
     * @param $institutionId | Id institution
     * @return array
     */
    /**
     * @param $institutionId | Id institution
     * @return array
     */
    protected function getAllProcessingCircuits($institutionId = null)
    {
        try {

            $circuits =   ClaimCategory::all()->map(function ($item) use ($institutionId){

                $item['claim_objects']  = ClaimObject::with(['units' => function($query) use ($institutionId){

                    $query->where("claim_object_unit.institution_id", "=", $institutionId);

                }])->where('claim_category_id', $item->id)->get();

                return $item;
            });

        } catch (\Exception $exception) {

            throw new CustomException("Impossible de récupérer les circuits de traitements");

        }

        return $circuits;
    }

    /**
     * @param null $institutionId
     * @return mixed
     */
    protected function getAllUnits($institutionId = null){

        try {

            $units = Unit::where('institution_id', $institutionId)->whereHas('unitType', function ($q){

                $q->where('can_treat', 1);

            })->get();

        } catch (\Exception $exception) {

            throw new CustomException("Impossible de récupérer la liste des unités.");

        }

        return $units;
    }

    /**
     * @param $request
     * @param $collection
     * @param null $institutionId
     * @return mixed
     */
    protected function rules($request, $collection, $institutionId = NULL){

        foreach ($request as $claim_object_id => $units_ids) {
            // Check if claim_object_id exists
            $claim_object = ClaimObject::findOrFail($claim_object_id);
            // Check if requirement_ids don't contain same values and exist
            $unit_ids_collection = collect([]);
            $unitsSync = [];

            if(!is_null($units_ids)){

                foreach ($units_ids as $unit_id) {

                    if ($unit_ids_collection->search($unit_id, true) !== false) {
                        throw new RetrieveDataUserNatureException($unit_id . " is sent more than once");
                    }

                    Unit::where('institution_id', $institutionId)->whereHas('unitType', function ($q){

                        $q->where('can_treat', 1);

                    })->findOrFail($unit_id);

                    $unit_ids_collection->push($unit_id);

                    $unitsSync[$unit_id] = ['institution_id' => $institutionId];

                }

            }

            $collection->push([
                'claim_object' => $claim_object,
                'units_ids' => $unitsSync,
            ]);

        }

        return $collection;
    }

    /**
     * @param $collection
     * @param null $institutionId
     * @return bool
     */
    protected function detachAttachUnits($collection , $institutionId = NULL){

        try{

            foreach ($collection as $key => $item) {

                $item['claim_object']->units()->whereHas('unitType', function ($q){

                    $q->where('can_treat', 1);

                })->wherePivot('institution_id', $institutionId)->sync($item['units_ids']);

            }


        } catch (\Exception $exception) {

            throw new CustomException("Impossible de mettre à jour les circuits de traitements.");
        }

        return true;
    }

}
