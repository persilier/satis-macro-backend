<?php


namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Eloquent\Collection;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\ActivePilot;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\StaffManagement;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Notifications\ReminderAfterDeadline;
use Satis2020\ServicePackage\Notifications\ReminderBeforeDeadline;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ConfigurationPilotTrait;

/**
 * Trait MonitoringClaim
 * @package Satis2020\ServicePackage\Traits
 */
trait MonitoringClaim
{
    use ConfigurationPilotTrait, DataUserNature, ConfigurationPilotTrait, StaffManagement;
    /**
     * @param $request
     * @param $status
     * @param bool $treatment
     * @return mixed
     */
    protected function getAllClaim($request, $status, $treatment = false)
    {
        try {

            $claims = $this->getAllDataFilter($request, $status, $treatment)->map(function ($item) {
                $item['time_expire'] = $this->timeExpire($item->created_at, $item->time_limit, $item->status);
                return $item;
            });
        } catch (\Exception $exception) {

            throw new CustomException("Impossible de récupérer des réclamations.");
        }

        return $claims;
    }


    /**
     * @param $createdDate
     * @param $timeLimit
     * @return mixed
     */
    protected function timeExpire($createdDate = false, $timeLimit = false, $status)
    {

        $diff = null;

        if ($timeLimit && $createdDate && ($status !== 'archived')) {

            $dateExpire = $createdDate->copy()->addWeekdays($timeLimit);
            $diff = now()->diffInDays(($dateExpire), false);
        }

        return $diff;
    }


    /**
     * @param $request
     * @param $status
     * @param bool $treatment
     * @return Builder
     */
    protected function getAllDataFilter($request, $status, $treatment)
    {
        $configuration  = $this->nowConfiguration()['configuration'];
        $lead_pilot  = $this->nowConfiguration()['lead_pilot'];
        $all_active_pilots  = $this->nowConfiguration()['all_active_pilots'];
        if ($configuration->many_active_pilot  === "1" && $this->staff()->id != $lead_pilot->id) {
            if (!in_array($status, [Claim::CLAIM_INCOMPLETE, Claim::CLAIM_ARCHIVED, Claim::CLAIM_FULL, Claim::CLAIM_TRANSFERRED_TO_TARGET_INSTITUTION, Claim::CLAIM_VALIDATED])) {
                $claims = Claim::with($this->getRelations())->whereHas('activeTreatment', function ($query) {
                    $query->where('transferred_to_unit_by', $this->staff()->id);
                });
            } else {
                $claims = Claim::with($this->getRelations());
            }
        } else {
            $claims = Claim::with($this->getRelations());
        }

        if ($request->has('institution_id')) {

            $claims->where('institution_targeted_id', $request->institution_id);
        }

        if ($request->has('type_client')) {

            $claims->whereHas('claimer', function ($o) use ($request) {

                $o->where('type_client', $request->type_client);
            });
        }


        if ($treatment) {

            $claims->join('treatments', function ($join) {

                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id');
            })->select('claims.*');
        }


        if ($status === 'transferred_to_targeted_institution') {

            $claims->where('status', 'full')->orWhere('status', 'transferred_to_targeted_institution');
        } else {

            $claims->where('status', $status);
        }


        return $claims->get();
    }


    /**
     * @param $claimId
     * @param bool $institutionId
     * @return Builder|Builder[]|Collection|Model
     */
    protected function getOne($claimId, $institutionId = false)
    {

        $claim = Claim::with($this->getRelations())->findOrFail($claimId);

        if ($institutionId) {
            if ($institutionId != $claim->institution_targeted_id)
                throw new CustomException("Impossible de récupérer des réclamations.");
        }

        $time_limit = $this->timeExpire($claim->created_at, $claim->time_limit, $claim->status);

        $claim = collect($claim)->toArray();

        $claim['time_expire'] = $time_limit;

        return $claim;
    }


    /**
     * @return array
     */
    protected function getRelations()
    {
        $relations = [
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment.satisfactionMeasuredBy.identite',
            'activeTreatment.responsibleStaff.identite', 'activeTreatment.assignedToStaffBy.identite', 'activeTreatment.responsibleUnit', 'activeTreatment.staffTransferredToUnitBy'
        ];

        return $relations;
    }

    /**
     * @param $request
     * @param bool $institutionId
     * @return array
     */
    protected function rules($request, $institutionId = false)
    {

        $data = [
            'institution_id' => 'sometimes|exists:institutions,id',
            'claim_category_id' => 'sometimes|exists:claim_categories,id',
            'claim_object_id' => 'sometimes|', Rule::exists('claim_objects', 'id')->where(function ($query) use ($request) {
                $query->where('id', $request->claim_category_id);
            }),
            'unit_id' => 'sometimes|', Rule::exists('units', 'id')->where(function ($query) use ($request) {
                $query->where('id', $request->unit_id)->where('institution_id', $request->institution_id);
            }),
            'staff_id' => 'sometimes|', Rule::exists('staff', 'id')->where(function ($query) use ($request) {
                $query->where('id', $request->staff_id)->where('institution_id', $request->institution_id);
            }),
            'date_start' => 'sometimes|date_format:Y-m-d',
            'date_end' => 'sometimes|date_format:Y-m-d|after_or_equal:date_start'
        ];

        return $data;
    }

    /**
     * @param $incompletes
     * @param $toAssignedToUnit
     * @param $toAssignedToUStaff
     * @param $awaitingTreatment
     * @param $toValidate
     * @param $toMeasureSatisfaction
     * @param bool $institutionId
     * @return array
     */
    protected function metaData($incompletes, $toAssignedToUnit, $toAssignedToUStaff, $awaitingTreatment, $toValidate, $toMeasureSatisfaction, $institutionId = false)
    {
        $conf_data   =  $this->nowConfiguration();
        $configuration  = $conf_data['configuration'];
        $lead_pilot  = $conf_data['lead_pilot'];
        $all_active_pilots  = $conf_data['all_active_pilots'];
        $data = [
            'incompletes' => $incompletes,
            'toAssignementToUnit' => $toAssignedToUnit,
            'toAssignementToStaff' => $toAssignedToUStaff,
            'awaitingTreatment' => $awaitingTreatment,
            'toValidate' => $toValidate,
            'toMeasureSatisfaction' => $toMeasureSatisfaction,
            'claimCategories' => ClaimCategory::all(),
            'claimObjects' => ClaimObject::all(),
        ];

        if ($institutionId) {

            $data['units'] = Unit::where('institution_id', $institutionId)->get();
            $data['staffs'] = Staff::with('identite')->where('institution_id', $institutionId)->get();
            $data['collectors'] = $this->getAllCollectors($institutionId);
            Log::info();
            if ($configuration->many_active_pilot  === "1" && $this->staff()->id == $lead_pilot->id) {

                $data['activePilots'] = $all_active_pilots;
            }
        } else {

            $data['institutions'] = Institution::all();
            $data['units'] = Unit::all();
            $data['staffs'] = Staff::with('identite')->get();
            $data['collectors'] = $this->getAllCollectors();

            if ($configuration->many_active_pilot  === "1") {

                $data['activePilots'] = ActivePilot::with('staff.identite')->get();
            }
        }


        return $data;
    }


    /**
     * @param $unit
     * @return mixed
     */
    protected function getIdentitesResponsibleUnit($unit)
    {

        return Identite::whereHas('staff', function ($query) use ($unit) {
            $query->where('unit_id', $unit->id);
        })->get();
    }

    /**
     * @param $staff
     * @return mixed
     */
    protected function getIdentitesResponsibleStaff($staff)
    {

        $lead = NULL;

        if ($staff->unit->lead) {

            $lead = $staff->unit->lead->id;
        }

        return Identite::whereHas('staff', function ($query) use ($staff, $lead) {

            $query->where('id', $staff->id)->orWhere('id', $lead);
        })->get();
    }


    /**
     * @param $treatment
     * @param $coef
     * @return array
     */
    protected function getAllClaimRelance($treatment, $coef)
    {
        $claims = Claim::with($this->getRelations());

        if ($treatment) {

            $claims->join('treatments', function ($join) {

                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id');
            })->select('claims.*');
        }

        return $claims->where('status', '!=', 'archived')
            ->orWhere('status', '!=', 'unfounded')
            ->whereNotNull('claim_object_id')
            ->get()->filter(function ($item) use ($coef) {
                if ($item->claimObject != null && now() >= $this->echeanceNotif($item->created_at, $item->claimObject->time_limit, $coef))

                    //if (now() >= $this->echeanceNotif($item->created_at, $item->time_limit, $coef))
                    return $item;
            })->all();
    }


    /**
     * @param bool $treatment
     * @return void
     */
    protected function treatmentRelance($treatment = false)
    {

        $coef = 100;

        if ($taux = Metadata::where('name', 'coef-relance')->first()) {

            $coef = json_decode($taux->data);
        }

        $claims = $this->getAllClaimRelance($treatment, $coef);

        foreach ($claims as $claim) {

            $identite = null;

            if ($claim->status === 'incomplete' || $claim->status === 'full' || $claim->status === 'transferred_to_targeted_institution') {
                try {
                    $identite = $this->getInstitutionPilot($claim->createdBy->identite->staff->institution);
                } catch (\Exception $exception) {
                    $identite = null;
                }
            }

            if ($claim->status === 'transferred_to_unit') {
                try {
                    $identite = $this->getIdentitesResponsibleUnit($claim->activeTreatment->responsibleUnit);
                } catch (\Exception $exception) {
                    $identite = null;
                }
            }

            if ($claim->status === 'assigned_to_staff') {

                $identite = $this->getIdentitesResponsibleStaff($claim->activeTreatment->responsibleStaff);
            }

            if ($claim->status === 'treated' || $claim->status === 'validated') {
                try {
                    $identite = $this->getInstitutionPilot($claim->activeTreatment->responsibleStaff->institution);
                } catch (\Exception $exception) {
                    $identite = null;
                }
            }

            if (!is_null($identite)) {

                $interval = $this->timeExpireRelance($claim->created_at, $claim->time_limit);
                $this->sendNotificationRelance($interval, $identite, $claim);
            }
        };
    }


    /**
     * @param $interval
     * @param $identite
     * @param $claim
     * @return mixed
     */
    protected function sendNotificationRelance($interval, $identite, $claim)
    {

        $time = $this->stringDateInterval($interval);

        if ($interval->invert === 1) {
            $notif = new ReminderAfterDeadline($claim, $time);
        } else {
            $notif = new ReminderBeforeDeadline($claim, $time);
        }


        $this->notificationRelance($identite, $notif);
    }

    /**
     * @param $identite
     * @param $notif
     */
    protected function notificationRelance($identite, $notif)
    {

        if ($identite instanceof Collection) {

            \Illuminate\Support\Facades\Notification::send($identite, $notif);
        } else {

            $identite->notify($notif);
        }
    }


    /**
     * @param $interval
     * @return string
     */
    protected function stringDateInterval($interval)
    {

        $message = '';

        if ($interval->y > 0) {
            $message .= $interval->y . 'an(s) ';
        }

        if ($interval->m > 0) {
            $message .= $interval->m . 'moi(s) ';
        }

        if ($interval->d > 0) {
            $message .= $interval->d . 'jour(s) ';
        }

        if ($interval->h > 0) {
            $message .= $interval->h . 'heure(s) ';
        }

        if ($interval->i > 0) {
            $message .= $interval->i . 'minute(s) ';
        }

        return $message;
    }

    /**
     * @param $createdDate
     * @param $timeLimit
     * @param $coef
     * @return mixed
     */
    protected function echeanceNotif($createdDate, $timeLimit, $coef)
    {

        $timeNotif = (($timeLimit * 86400) * $coef) / 100;

        $timeNotif = $this->convertSecondeJHMS($timeNotif);

        $echeanceNotif = $createdDate->copy()->addWeekdays($timeNotif['j'])->addHours($timeNotif['h'])->addMinutes($timeNotif['m'])->addSeconds($timeNotif['s']);

        return $echeanceNotif;
    }


    /**
     * @param $createdDate
     * @param $timeLimit
     * @return null
     */
    protected function timeExpireRelance($createdDate, $timeLimit)
    {

        $echeance = $createdDate->copy()->addWeekdays($timeLimit);

        $diff = now()->copy()->diff($echeance, false);

        return $diff;
    }


    /**
     * @param $seconde
     * @return array
     */
    protected function convertSecondeJHMS($seconde)
    {
        $jour = 0;
        $heure = 0;
        $minute = 0;

        while ($seconde >= 86400) {
            $jour = $jour + 1;
            $seconde = $seconde - 86400;
        }
        while ($seconde >= 3600) {
            $heure = $heure + 1;
            $seconde = $seconde - 3600;
        }
        while ($seconde >= 60) {
            $minute = $minute + 1;
            $seconde = $seconde - 60;
        }

        return [
            'j' => $jour,
            'h' => $heure,
            'm' => $minute,
            's' => $seconde
        ];
    }
}
