<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
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
     * @throws CustomException
     */
    protected function getAllProcessingCircuits($institutionId = null)
    {
        try {

            $circuits = ClaimCategory::with(['claimObjects.units' => function ($rel) use ($institutionId) {

                $rel->wherePivot('institution_id', $institutionId);

            }])->has('claimObjects')->get();


        } catch (\Exception $exception) {

            throw new CustomException("Impossible de récupérer les circuits de traitements");

        }

        return $circuits;
    }

    /**
     * @param null $institutionId
     * @return mixed
     */
    protected function getAllUnits($institutionId = null)
    {

        try {

            $units = Unit::where('institution_id', $institutionId)->whereHas('unitType', function ($q) {

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
     * @throws RetrieveDataUserNatureException
     */
    protected function rules($request, $collection, $institutionId = NULL)
    {

        $claimObjects = ClaimObject::query()->get();

        $units = Unit::query()
            ->where('institution_id', $institutionId)
            ->whereHas('unitType', function ($q) {
                $q->where('can_treat', 1);
            })
            ->get();

        foreach ($request as $claim_object_id => $units_ids) {

            $claim_object = $claimObjects->find($claim_object_id);

            if (!$claim_object) {
                throw new RetrieveDataUserNatureException($claim_object_id . " does not reference a valid claim object");
            }

            $unit_ids_collection = collect([]);
            $unitsSync = [];

            if ($units_ids) {

                foreach ($units_ids as $unit_id) {

                    if ($unit_ids_collection->search($unit_id, true) !== false) {
                        throw new RetrieveDataUserNatureException($unit_id . " is sent more than once");
                    }

                    $unit = $units->find($unit_id);

                    if (!$unit) {
                        throw new RetrieveDataUserNatureException($unit_id . " is not a valid unit with unitType can treat");
                    }

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
     * @throws CustomException
     */
    protected function detachAttachUnits($collection, $institutionId = NULL)
    {

        try {

            DB::table('claim_object_unit')->where('institution_id', '=', $institutionId)
                ->delete();

            foreach ($collection as $key => $item) {

                $item['claim_object']->units()->attach($item['units_ids']);

            }


        } catch (\Exception $exception) {

            throw new CustomException($exception->getMessage());
        }

        return true;
    }

}
