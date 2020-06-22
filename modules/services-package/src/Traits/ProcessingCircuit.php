<?php


namespace Satis2020\ServicePackage\Traits;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Unit;
trait ProcessingCircuit
{

    /**
     * @param $status| Claim complete - status=full | Claim incomplete - status=incomplete
     * @param $institutionId | Id institution
     * @return array
     * @throws CustomException
     */
    protected function getAllProcessingCircuits($institutionId=null)
    {
        try {
            $circuits = ClaimCategory::with(['claimObjects.units' => function ($query) use ($institutionId){
                    $query->whereHas('claimObjects', function ($q) use ($institutionId){
                        $q->whereHas('units', function ($p) use ($institutionId){
                            $p->where('institution_id', $institutionId);
                        });
                    });
                }])->get();

        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer les circuits de traitements");
        }
        return $circuits;
    }

    protected function getAllUnits($institutionId = null){
        try {

            $units = Unit::where('institution_id', $institutionId)->get();

        } catch (\Exception $exception) {
            throw new CustomException("Impossible de récupérer la liste des unités.");
        }
        return $units;
    }

    protected function rules($request, $collection, $institutionId = null){

        foreach ($request as $claim_object_id => $units_ids) {
            // Check if claim_object_id exists
            $claim_object = ClaimObject::findOrFail($claim_object_id);
            // Check if requirement_ids don't contain same values and exist
            $unit_ids_collection = collect([]);
            foreach ($units_ids as $unit_id) {

                if ($unit_ids_collection->search($unit_id, true) !== false) {
                    throw new RetrieveDataUserNatureException($unit_id . " is sent more than once");
                }

                Unit::where('institution_id',$institutionId)->findOrFail($unit_id);

                $unit_ids_collection->push($unit_id);
            }
            //dd($claim_object);
            $collection->push([
                'claim_object' => $claim_object,
                'units_ids' => $units_ids
            ]);

        }

        return $collection;
    }

    protected function detachAttachUnits($collection , $institutionId=null){

        try {

            $collection->each(function ($item, $key) use ($institutionId){

                $attachedIds = $item['claim_object']->units()->where('institution_id', $institutionId)->pluck('id');

                $item['claim_object']->units()->detach($attachedIds);
                $item['claim_object']->units()->attach($item['units_ids']);
            });

        } catch (\Exception $exception) {
            throw new CustomException("Impossible de mettre à jour les circuits de traitements.");
        }

        return true;
    }

}