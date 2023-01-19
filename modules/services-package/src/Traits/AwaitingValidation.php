<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Satis2020\ServicePackage\Models\Claim;

trait AwaitingValidation
{

    protected function getClaimsAwaitingValidationInMyInstitution($paginate = false, $paginationSize = 10, $key = null, $type = null, $institution_id = null)
    {

        $institution_id = is_null($institution_id)
            ? $this->institution()->id
            : $institution_id;

        $claimsTreated = Claim::with($this->getRelations())->where('status', 'treated')
            ->whereHas('activeTreatment.responsibleStaff', function ($query) use ($institution_id) {
                $query->where('institution_id', $institution_id);
            });

        if ($paginate) {

            if ($key) {
                switch ($type) {
                    case 'reference':
                        $claimsTreated = $claimsTreated->where('reference', 'LIKE', "%$key%");
                        break;
                    case 'claimObject':
                        $claimsTreated = $claimsTreated->whereHas("claimObject", function ($query) use ($key) {
                            $query->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                        });
                        break;
                    case 'transferred_to_unit_by':
                        $claimsTreated = $claimsTreated->whereHas("activeTreatment", function ($query) use ($key) {
                            $query->where("transferred_to_unit_by", $key);
                        });
                        break;

                    default:
                        $claimsTreated = $claimsTreated->whereHas("claimer", function ($query) use ($key) {
                            $query->where('firstname', 'like', "%$key%")
                                ->orWhere('lastname', 'like', "%$key%")
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

    protected function getClaimsAwaitingValidationInMyInstitutionWithParams($paginate = false, $paginationSize = 10, $key = null, $type = null, $institution_id = null, $status = null, $transferred_to_unit_by=null)
    {

        $institution_id = is_null($institution_id)
            ? $this->institution()->id
            : $institution_id;

        $claimsTreated = Claim::with($this->getRelations());
        if ($status) {
            $claimsTreated->where('status', 'treated');
        }


        $claimsTreated->whereHas('activeTreatment.responsibleStaff', function ($query) use ($institution_id) {
            $query->where('institution_id', $institution_id);
        });
        if ($transferred_to_unit_by){
            $claimsTreated->whereHas('activeTreatment', function ($query)  {
                $query->where('transferred_to_unit_by', '<>' ,null);
            });
        }

        if ($paginate) {

            if ($key) {
                switch ($type) {
                    case 'reference':
                        $claimsTreated = $claimsTreated->where('reference', 'LIKE', "%$key%");
                        break;
                    case 'claimObject':
                        $claimsTreated = $claimsTreated->whereHas("claimObject", function ($query) use ($key) {
                            $query->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                        });
                        break;
                    case 'transferred_to_unit_by':
                        $claimsTreated = $claimsTreated->whereHas("activeTreatment", function ($query) use ($key) {
                            $query->where("transferred_to_unit_by", $key);
                        });
                        break;

                    default:
                        $claimsTreated = $claimsTreated->whereHas("claimer", function ($query) use ($key) {
                            $query->where('firstname', 'like', "%$key%")
                                ->orWhere('lastname', 'like', "%$key%")
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

    protected function getClaimsAwaitingValidationInMyInstitutionWithConfig($configs, $staff, $institution, $paginate = true, $paginationSize = 50, $key = null, $type = null)
    {
        if ($configs["configuration"]["many_active_pilot"]) {
            if ($staff->id == $institution->active_pilot_id) {
                if ($type) {
                    $claimsTreated = $this->getClaimsAwaitingValidationInMyInstitution($paginate, $paginationSize, $key, $type);
                    return $claimsTreated;
                } else {
                    $claimsTreated = $this->getClaimsAwaitingValidationInMyInstitution($paginate, $paginationSize, $staff->id, "transferred_to_unit_by");
                    return $claimsTreated;
                }
            } else {
                $claimsTreated = $this->getClaimsAwaitingValidationInMyInstitution($paginate, $paginationSize, $staff->id, "transferred_to_unit_by");
                return $claimsTreated;
            }
        } else {
            $claimsTreated = $this->getClaimsAwaitingValidationInMyInstitution($paginate, $paginationSize, $key, $type);
            return $claimsTreated;
        }
    }

    protected function getClaimsTransferredInMyInstitutionWithConfig($configs, $staff, $institution, $paginate = true, $paginationSize = 50, $key = null, $type = null)
    {
        if ($configs["configuration"]["many_active_pilot"]) {
            if ($staff->id == $institution->active_pilot_id) {
                if ($type) {
                    $claimsTreated = $this->getClaimsAwaitingValidationInMyInstitutionWithParams($paginate, $paginationSize, $key, $type,$institution->id,null, true);
                    return $claimsTreated;
                } else {
                    $claimsTreated = $this->getClaimsAwaitingValidationInMyInstitutionWithParams($paginate, $paginationSize, $staff->id, "transferred_to_unit_by",$institution->id,null, true);
                    return $claimsTreated;
                }
            } else {
                $claimsTreated = $this->getClaimsAwaitingValidationInMyInstitutionWithParams($paginate, $paginationSize, $staff->id, "transferred_to_unit_by",$institution->id,null, true);
                return $claimsTreated;
            }
        } else {
            $claimsTreated = $this->getClaimsAwaitingValidationInMyInstitutionWithParams($paginate, $paginationSize, $key, $type,$institution->id,null, true);
            return $claimsTreated;
        }
    }

    protected function getClaimsAwaitingValidationInAnyInstitution($paginate = false, $paginationSize = 10, $key = null, $type = null)
    {
        $claimsTreated = Claim::with($this->getRelations())->where('status', 'treated')
            ->whereHas('activeTreatment', function ($query) {
                $query->with($this->getActiveTreatmentRelations());
            });

        if ($paginate) {

            if ($key) {
                switch ($type) {
                    case 'reference':
                        $claimsTreated = $claimsTreated->where('reference', 'LIKE', "%$key%");
                        break;
                    case 'claimObject':
                        $claimsTreated = $claimsTreated->whereHas("claimObject", function ($query) use ($key) {
                            $query->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                        });
                        break;
                    default:
                        $claimsTreated = $claimsTreated->whereHas("claimer", function ($query) use ($key) {
                            $query->where('firstname', 'like', "%$key%")
                                ->orWhere('lastname', 'like', "%$key%")
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
            'activeTreatment',
            'activeTreatment.staffTransferredToUnitBy.identite',

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

        if (!is_null($claim->activeTreatment->declared_unfounded_at)) {
            // the claim is declared unfounded
            $claim->update(['status' => 'archived']);
            $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolutionUnfounded($claim));
        } else { // the claim is solved
            $claim->update(['status' => 'validated']);
            $claim->claimer->notify(new \Satis2020\ServicePackage\Notifications\CommunicateTheSolution($claim));
        }

        $this->activityLogService->store("Une réclamation a été validée",
            $this->institution()->id,
            $this->activityLogService::VALIDATED_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

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

        $this->activityLogService->store("Une réclamation a été invalide",
            $this->institution()->id,
            $this->activityLogService::INVALIDATED_CLAIM,
            'claim',
            $this->user(),
            $claim
        );

        return $claim;
    }

    protected function showClaim($claim)
    {
        $claim->load($this->getRelations());
        $claim->activeTreatment->load($this->getActiveTreatmentRelations());
        return $claim;
    }
}
