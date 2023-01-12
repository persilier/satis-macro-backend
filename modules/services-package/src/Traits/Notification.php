<?php


namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ActivePilot;
use Satis2020\ServicePackage\Channels\MessageChannel;

trait Notification
{

    use ActivePilot;

    protected function getNotification($event)
    {
        return collect(json_decode(Metadata::ofName('notifications')->firstOrFail()->data))
            ->first(function ($item, $key) use ($event) {
                return $item->event == $event;
            });
    }

    protected function getFeedBackChannels($staff)
    {
        $channels = collect($staff->feedback_preferred_channels);

        return $channels->isEmpty()
            ? []
            : $channels->map(function ($item, $key) {
                return $item == 'sms' ? MessageChannel::class : 'mail';
            })->all();
    }

    protected function getInstitutionPilot($institution = null)
    {

        $roleName = null;

        if (!is_null($institution)) {
            $roleName = $this->getPilotRoleNameByInstitution($institution);
        }

        if (is_null($roleName)) {
            return null;
        }

        try {
            return User::with('identite.staff')
                ->get()
                ->first(function ($value, $key) use ($institution, $roleName) {
                    return ($institution->institutionType->name == 'observatory' || $institution->institutionType->name == 'membre')
                        ? $value->hasRole($roleName) && $value->identite->staff->is_active_pilot
                        : $value->identite->staff->institution_id == $institution->id && $value->hasRole($roleName) && $value->identite->staff->is_active_pilot;
                })->identite;
        } catch (\Exception $exception) {
            return null;
        }
    }

    protected function getStaffInstitutionMessageApi($institution)
    {
        try {
            return $institution->institutionType->name == 'membre'
                ? Institution::with('institutionMessageApi', 'institutionType')
                    ->get()
                    ->first(function ($value, $key) {
                        return $value->institutionType->name == 'observatory';
                    })->institutionMessageApi
                : $institution->institutionMessageApi;
        } catch (\Exception $exception) {
            return null;
        }

    }

    protected function getUnitStaffIdentities($unitId)
    {
        return Staff::with('unit', 'identite.user')
            ->get()
            ->filter(function ($value, $key) use ($unitId) {

                if (is_null($value->unit) || is_null($value->identite)) {
                    return false;
                }

                if (is_null($value->identite->user)) {
                    return false;
                }

                return $value->unit->id == $unitId && $value->identite->user->hasRole('staff');
            })
            ->pluck('identite')
            ->values();
    }

    protected function getStaffIdentities($staffIds, $exceptIds = [])
    {
        return Staff::with('identite')
            ->get()
            ->filter(function ($value, $key) use ($staffIds, $exceptIds) {
                return is_null($value->identite)
                    ? false
                    : in_array($value->id, $staffIds) && !in_array($value->id, $exceptIds);
            })
            ->pluck('identite')
            ->values();
    }

    protected function getNotificationStatus($notificationType)
    {
        $data = [
            'RegisterAClaim' => ['incomplete', 'full'],
            'CompleteAClaim' => ['full'],
            'TransferredToTargetedInstitution' => ['transferred_to_targeted_institution'],
            'TransferredToUnit' => ['transferred_to_unit'],
            'RejectAClaim' => ['transferred_to_targeted_institution', 'full'],
            'AssignedToStaff' => ['assigned_to_staff'],
            'TreatAClaim' => ['treated'],
            //'ValidateATreatment' => ['archived', 'validated'],
            'InvalidateATreatment' => ['assigned_to_staff'],
            'AddContributorToDiscussion' => ['assigned_to_staff', 'treated', 'validated'],
        ];

        return $data[$notificationType];
    }

    protected function remove_accent($string)
    {
        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');

        return strtr($string, $unwanted_array);
    }

    protected function canSendRecurrenceNotification($institutionId)
    {
        $recurrenceAlertSettings = json_decode(Metadata::ofName('recurrence-alert-settings')->firstOrFail()->data);

        $numberOfClaims = Claim::with(['createdBy'])
            ->whereBetween('created_at', [
                Carbon::now()->subDays($recurrenceAlertSettings->recurrence_period)->format('Y-m-d H:i:s'),
                Carbon::now()->format('Y-m-d H:i:s')
            ])
            ->get()
            ->filter(function ($claim, $key) use ($institutionId) {
                return is_null($claim->createdBy) ? $claim->institution_targeted_id == $institutionId :
                    $claim->createdBy->institution_id == $institutionId;
            })
            ->count();

        return $numberOfClaims >= $recurrenceAlertSettings->max;

    }

    protected function getStaffToReviveIdentities(Claim $claim)
    {
        $identities = [];

        $statusAssociatedToCreatedByPilot = [
            'incomplete',
            'full'
        ];

        $statusAssociatedToTargetedInstitutionPilot = [
            'transferred_to_targeted_institution'
        ];

        $statusAssociatedToCreatedBy = [
            'incomplete'
        ];

        $statusAssociatedToTransferredToUnitPilot = [
            'rejected',
            'treated',
            'validated',
        ];

        $statusAssociatedToUnitLead = [
            'transferred_to_unit'
        ];

        $statusAssociatedToStaff = [
            'assigned_to_staff'
        ];

        // On récupère l'identité du pilot de l'institution qui a créé la réclamation lorsque la réclamation est incomplete|full
        if (in_array($claim->status, $statusAssociatedToCreatedByPilot)) {

            if (is_null($claim->createdBy)) {
                $institution = $claim->institutionTargeted;
            } else {
                $institution = $claim->createdBy->institution;
            }

            $identity = $this->getInstitutionPilot($institution);
            if (!is_null($identity))
                $identities[$identity->id] = $identity;
        }

        // On récupère l'identité du pilot de l'institution ciblée par la réclamation lorsque la réclamation est transferred_to_targeted_institution
        if (in_array($claim->status, $statusAssociatedToTargetedInstitutionPilot)) {

            if (!is_null($claim->institutionTargeted)) {
                $identity = $this->getInstitutionPilot($claim->institutionTargeted);
                if (!is_null($identity))
                    $identities[$identity->id] = $identity;
            }

        }

        // On récupère l'identité du pilot qui a transferé la réclamation
        if (in_array($claim->status, $statusAssociatedToTransferredToUnitPilot)) {

            if (!is_null($claim->activeTreatment)) {

                $institution = null;

                if (!is_null($claim->activeTreatment->transferred_to_targeted_institution_at)) {
                    $institution = $claim->institutionTargeted;
                } else {

                    if (is_null($claim->createdBy)) {
                        $institution = $claim->institutionTargeted;
                    } else {
                        $institution = $claim->createdBy->institution;
                    }
                }

                $identity = $this->getInstitutionPilot($institution);
                if (!is_null($identity))
                    $identities[$identity->id] = $identity;
            }

        }

        // On récupère l'identité du staff qui est le lead de l'unité à laquelle la réclamation a été transférée
        if (in_array($claim->status, $statusAssociatedToUnitLead)) {

            if (!is_null($claim->activeTreatment)) {

                if (!is_null($claim->activeTreatment->responsibleUnit)) {

                    if (!is_null($claim->activeTreatment->responsibleUnit->lead)) {

                        if (!is_null($claim->activeTreatment->responsibleUnit->lead->identite)) {
                            $identities[$claim->activeTreatment->responsibleUnit->lead->identite->id] = $claim->activeTreatment->responsibleUnit->lead->identite;
                        }

                    }
                }

            }

        }

        // On récupère l'identité du staff chargé du traitement de la réclamation
        if (in_array($claim->status, $statusAssociatedToStaff)) {

            if (!is_null($claim->activeTreatment)) {

                if (!is_null($claim->activeTreatment->responsibleStaff)) {

                    if (!is_null($claim->activeTreatment->responsibleStaff->identite))
                        $identities[$claim->activeTreatment->responsibleStaff->identite->id] = $claim->activeTreatment->responsibleStaff->identite;

                }
            }

        }

        // On récupère l'identité du staff ayant enregistré la réclamation
        if (in_array($claim->status, $statusAssociatedToCreatedBy)) {

            if (!is_null($claim->createdBy)) {

                if (!is_null($claim->createdBy->identite))
                    $identities[$claim->createdBy->identite->id] = $claim->createdBy->identite;

            } else {

                $identity = $this->getInstitutionPilot($claim->institutionTargeted);
                if (!is_null($identity))
                    $identities[$identity->id] = $identity;
            }

        }
        
        return array_values($identities);

    }
}
