<?php

namespace Satis2020\ServicePackage\Services\ActivityLog;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Repositories\ActivityLogRepository;
use Satis2020\ServicePackage\Repositories\HistoryPasswordRepository;
use Satis2020\ServicePackage\Repositories\UserRepository;

/***
 * Class ActivityLogService
 * @package Satis2020\ServicePackage\Services\ActivityLog
 */
class ActivityLogService
{
    const CREATED = 'CREATED';
    const UPDATED = 'UPDATED';
    const DELETED = 'DELETED';

    /***
     * @var $activityLogRepository
     */
    protected  $activityLogRepository;

    protected $userRepository;

    /***
     * ActivityLogService constructor.
     * @param ActivityLogRepository $activityLogRepository
     * @param UserRepository $userRepository
     */
    public function __construct(ActivityLogRepository $activityLogRepository, UserRepository $userRepository)
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
        return [
            self::CREATED => 'Création',
            self::UPDATED => 'Modification',
            self::DELETED => 'Suppression'
        ];
    }

}