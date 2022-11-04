<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Models\Claim;

trait AwaitingValidation
{
    protected function getClaimsAwaitingValidationInMyInstitution($paginate = false, $paginationSize = 10, $key = null, $type = null, $institution_id = null)
    {

        $institution_id = is_null($institution_id)
            ? $this->institution()->id
            : $institution_id;

        $claimsTreated = Claim::with($this->getRelations())->where('status', 'treated')
         ->whereHas('activeTreatment.responsibleStaff', function ($query) use ($institution_id){ $query->where('institution_id',$institution_id);});

           if ($paginate) {

            if ($key) {
                switch ($type){
                    case 'reference':
                        $claimsTreated = $claimsTreated->where('reference', 'LIKE', "%$key%");
                        break;
                    case 'claimObject':
                        $claimsTreated = $claimsTreated->whereHas("claimObject",function ($query) use ($key){
                            $query->where("name->".App::getLocale(), 'LIKE', "%$key%");
                        });
                        break;
                    default:
                        $claimsTreated = $claimsTreated->whereHas("claimer",function ($query) use ($key){
                            $query->where('firstname' , 'like', "%$key%")
                                ->orWhere('lastname' , 'like', "%$key%")
                                ->orwhereJsonContains('telephone', $key)
                                ->orwhereJsonContains('email', $key);
                        });
                        break;
                }
            }

              return $claimsTreated->paginate($paginationSize);

           } else {

             return $claimsTreated->get();

           }

            /*$institution_id = is_null($institution_id)
            ? $this->institution()->id
            : $institution_id;

            $claimsTreated = Claim::with($this->getRelations())->where('status', 'treated')->get();
            return $claimsTreated->filter(function ($value, $key) use ($institution_id) {
                $value->activeTreatment->load($this->getActiveTreatmentRelations());
                return $value->activeTreatment->responsibleStaff->institution_id == $institution_id;
            });*/

    }

    /**
     * @return array
     */
    protected function getRelations()
    {
        return [
            'claimObject.claimCategory',
            'claimer',
            'relationship',
            'accountTargeted',
            'institutionTargeted',
            'unitTargeted',
            'requestChannel',
            'responseChannel',
            'amountCurrency',
            'createdBy.identite',
            'completedBy.identite',
            'files',
            'activeTreatment.satisfactionMeasuredBy.identite',
            'activeTreatment.responsibleStaff.identite',
            'activeTreatment.assignedToStaffBy.identite',
            'activeTreatment.responsibleUnit'
        ];
    }

    protected function getActiveTreatmentRelations()
    {
        return [
            'responsibleUnit',
            'assignedToStaffBy.identite',
            'responsibleStaff.identite',
            'satisfactionMeasuredBy.identite',
        ];
    }

    /**
     * @param $request
     * @param $claim
     * @return mixed
     */
    protected function handleValidate($request, $claim)
    {
        $validationData = [
            'invalidated_reason' => NULL,
            'validated_at' => Carbon::now()
        ];

        $backup = $this->backupData($claim, $validationData);

        $claim->activeTreatment->update([
            'solution_communicated' => $request->solution_communicated,
            'validated_at' => Carbon::now(),
            'invalidated_reason' => NULL,
            'treatments' => $backup
        ]);

        if(!is_null($claim->activeTreatment->declared_unfounded_at)){
            // the claim is declared unfounded
            $claim->update(['status' => 'archived']);
            $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolutionUnfounded($claim));
        } else { // the claim is solved
            $claim->update(['status' => 'validated']);
            $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolution($claim));
        }

        if (!is_null($claim->activeTreatment->responsibleStaff)) {
            if (!is_null($claim->activeTreatment->responsibleStaff->identite)) {
                $claim->activeTreatment->responsibleStaff->identite->notify(new \Satis2020\ServicePackage\Notifications\ValidateATreatment($claim));
            }
        }


        return $claim;
    }

    protected function handleInvalidate($request, $claim)
    {
        $validationData = [
            'invalidated_reason' => $request->invalidated_reason,
            'validated_at' => Carbon::now()
        ];

        $backup = $this->backupData($claim, $validationData);

        $claim->activeTreatment->update([
            'invalidated_reason' => $validationData['invalidated_reason'],
            'validated_at' => $validationData['validated_at'],
            'solved_at' => NULL,
            'declared_unfounded_at' => NULL,
            'treatments' => $backup
        ]);

        $claim->update(['status' => 'assigned_to_staff']);

        if (!is_null($claim->activeTreatment->responsibleStaff)) {
            if (!is_null($claim->activeTreatment->responsibleStaff->identite)) {
                $claim->activeTreatment->responsibleStaff->identite->notify(new \Satis2020\ServicePackage\Notifications\InvalidateATreatment($claim));
            }
        }


        return $claim;
    }

    protected function showClaim($claim)
    {
        $claim->load($this->getRelations());
        $claim->activeTreatment->load($this->getActiveTreatmentRelations());
        return $claim;
    }
}
