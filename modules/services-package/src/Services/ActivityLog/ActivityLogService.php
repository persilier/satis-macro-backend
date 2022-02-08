<?php

namespace Satis2020\ServicePackage\Services\ActivityLog;

use Satis2020\ServicePackage\Repositories\ActivityLogRepository;
use Satis2020\ServicePackage\Repositories\UserRepository;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\Contracts\Activity;

/***
 * Class ActivityLogService
 * @package Satis2020\ServicePackage\Services\ActivityLog
 */
class ActivityLogService
{
    const CLAIM_REVOKED = 'CLAIM_REVOKED';
    const CLAIM_UPDATED = 'CLAIM_UPDATED';
    const MEASURE_SATISFACTION = 'MEASURE_SATISFACTION';
    const CREATED = 'CREATED';
    const STAFF_CREATED = 'STAFF_CREATED';
    const UPDATED = 'UPDATED';
    const DELETED = 'DELETED';
    const UPDATE_PILOT_ACTIVE = 'UPDATE_PILOT_ACTIVE';
    const UPDATE_STAFF = 'UPDATE_STAFF';
    const REGISTER_CLAIM = 'REGISTER_CLAIM';
    const COMPLETION_CLAIM = 'COMPLETION_CLAIM';
    const TRANSFER_TO_INSTITUTION = 'TRANSFER_TO_INSTITUTION';
    const TRANSFER_TO_UNIT = 'TRANSFER_TO_UNIT';
    const TREATMENT_CLAIM = 'TREATMENT_CLAIM';
    const FUSION_CLAIM = "FUSION_CLAIM";
    const UNFOUNDED_CLAIM = 'UNFOUNDED_CLAIM';
    const REJECTED_CLAIM = "REJECTED_CLAIM";
    const AUTO_ASSIGNMENT_CLAIM = 'AUTO_ASSIGNMENT_CLAIM';
    const ASSIGNMENT_CLAIM = "ASSIGNMENT_CLAIM";
    const REASSIGNMENT_CLAIM = "REASSIGNMENT_CLAIM";
    const VALIDATED_CLAIM = 'VALIDATE_CLAIM';
    const INVALIDATED_CLAIM = 'INVALIDATED_CLAIM';
    const AUTH = 'AUTH';
    const LOGOUT = 'LOGOUT';
    const LOGIN = 'LOGIN';
    const ATTEMPT_LOGIN = 'ATTEMPT_LOGIN';
    const IMPORTATION = 'IMPORTATION';
    const NEW_USER_CREATED = 'NEW_USER_CREATED';
    /***
     * @var $activityLogRepository
     */
    protected  $activityLogRepository;

    protected $userRepository;

    protected $actionLogService;

    /***
     * ActivityLogService constructor.
     * @param ActivityLogRepository $activityLogRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        ActivityLogRepository $activityLogRepository,
        UserRepository $userRepository
    )
    {
        $this->activityLogRepository = $activityLogRepository;
        $this->userRepository = $userRepository;
    }

    /***
     * @param $institutionId
     * @param $paginate
     * @return mixed
     */
    public function allActivity($institutionId, $paginate)
    {
        return $this->activityLogRepository->getByInstitution($institutionId, $paginate);
    }

    /***
     * @param $institutionId
     * @param $request
     * @param $paginate
     * @return mixed
     */
    public function allActivityFilters($institutionId, $request, $paginate)
    {
        return $this->activityLogRepository->getByInstitutionFilters($institutionId, $request, $paginate);
    }

    /***
     * @param $institutionId
     * @return array
     */
    public function getDataForFiltering($institutionId)
    {
        return [
            'causers' => $this->userRepository->getUserByInstitution($institutionId),
            'log_actions' => $this->getAllAction()
        ];
    }

    /***
     * @return string[]
     */
    protected function getAllAction()
    {
         $actions = [
            self::CREATED => 'Enregistrement',
            self::UPDATED => 'Modification',
            self::DELETED => 'Suppression',
            self::UPDATE_PILOT_ACTIVE => 'Mise à jour du pilot actif',
            self::REGISTER_CLAIM => "Enregistrement d'une réclamation",
            self::COMPLETION_CLAIM => "Complétion d'une réclamation",
            self::TRANSFER_TO_UNIT => "Transfert vers une unité",
            self::FUSION_CLAIM => "Fusion",
            self::TREATMENT_CLAIM => 'Traitement',
            self::UNFOUNDED_CLAIM => 'Non fondée',
            self::REJECTED_CLAIM => 'Rejet de réclamation',
            self::AUTO_ASSIGNMENT_CLAIM => 'Auto-affectation',
            self::ASSIGNMENT_CLAIM => 'Affectation',
            self::REASSIGNMENT_CLAIM => 'Réaffectation',
            self::VALIDATED_CLAIM => 'Validation',
            self::INVALIDATED_CLAIM => 'Invalidation',
            self::AUTH => 'Authentification',
            self::NEW_USER_CREATED => "Nouvel uitilisateur créé",
            self::LOGIN => "Connexion",
            self::LOGOUT => "Déconnexion",
            self::STAFF_CREATED => "Création de staff",
            self::MEASURE_SATISFACTION => "Mésure de satisfaction",
            self::UPDATE_STAFF => "Mise à jour de staff",
        ];

         if (config('services.app_nature')=="MACRO") {
             $actions[self::TRANSFER_TO_INSTITUTION] = "Transfert vers une institution";
         }

         return $actions;
    }

    /***
     * @param $description
     * @param $institutionId
     * @param $logAction
     * @param string $logName
     * @param $causer
     * @param $subject
     * @return ActivityLogger
     */
    public function store($description,
          $institutionId,
          $logAction,
          $logName = 'default',
          $causer = null,
          $subject = null
    )
    {
        $activity = activity();

        if ($causer) {
            $activity = $activity->causedBy($causer);
        }

        if ($subject) {
            $activity = $activity->performedOn($subject);
        }

        $activity->tap(function(Activity $tap) use ($institutionId, $logAction, $logName) {
            $tap->ip_address = request()->ip();
            $tap->institution_id = $institutionId;
            $tap->log_name = $logName;
            $tap->log_action = $logAction;
        })->log($description);

        return $activity;
    }

    /**
     * @param $user_id
     * @param $action_type
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getLastLogByUserAndAction($user_id, $action_type)
    {
        return $this->activityLogRepository
            ->getLastLogByUserAndAction($user_id,$action_type);
    }
}