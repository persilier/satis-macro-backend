<?php

namespace Satis2020\ServicePackage\Services\NotificationProof;

use Satis2020\ServicePackage\Models\NotificationProof;
use Satis2020\ServicePackage\Repositories\NotificationProofRepository;

/***
 * Class ActivityLogService
 * @package Satis2020\ServicePackage\Services\ActivityLogService
 */
class NotificationProofService
{


    /**
     * @var NotificationProofRepository
     */
    private $proofRepository;

    /***
     * ActivityLogService constructor.
     * @param NotificationProofRepository $proofRepository
     */
    public function __construct(NotificationProofRepository $proofRepository)
    {
        $this->proofRepository = $proofRepository;
    }

    /***
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
     * @param $request
     * @param $paginate
     * @return mixed
     */
    public function filterInstitutionNotificationProofTtoExport($institutionId, $request)
    {
        return $this->proofRepository->getByInstitutionAndFilterToExport($institutionId, $request);
    }

    /***
     * @param $institutionId
     * @param $data
     * @return NotificationProof
     */
    public function store($institutionId,$data)
    {
        $data['institution_id'] = $institutionId;
        return $this->proofRepository->create($data);
    }

}