<?php

namespace Satis2020\ServicePackage\Services\ActivityLog;

use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\Repositories\ActivityLogRepository;
use Satis2020\ServicePackage\Repositories\NotificationProofRepository;
use Satis2020\ServicePackage\Repositories\UserRepository;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\Contracts\Activity;

/***
 * Class NotificationProofService
 * @package Satis2020\ServicePackage\Services\NotificationProofService
 */
class NotificationProofService
{


    /**
     * @var NotificationProofRepository
     */
    private $proofRepository;

    /***
     * NotificationProofService constructor.
     * @param NotificationProofRepository $proofRepository
     */
    public function __construct(NotificationProofRepository $proofRepository)
    {
        $this->proofRepository = $proofRepository;
    }

    /***
     * @param $institutionId
     * @param $paginate
     * @return mixed
     */
    public function allNotificationProof($paginate)
    {
        return $this->proofRepository->getAll($paginate);
    }

    /***
     * @param $institutionId
     * @param $request
     * @param $paginate
     * @return mixed
     */
    public function filterNotificationProofs( $request, $paginate)
    {
        return $this->proofRepository->getAllAndFilter( $request, $paginate);
    }

    /***
     * @param $institutionId
     * @param $request
     * @param $paginate
     * @return mixed
     */
    public function filterInstitutionNotificationProofs($institutionId, $request, $paginate)
    {
        return $this->proofRepository->getByInstitutionAndFilter($institutionId, $request, $paginate);
    }

    /***
     * @param $institutionId
     * @param $data
     * @return ActivityLogger
     */
    public function store($institutionId,$data)
    {
        $data['institution_id'] = $institutionId;
        return $this->proofRepository->create($data);
    }

}