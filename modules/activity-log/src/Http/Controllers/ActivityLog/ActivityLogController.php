<?php

namespace Satis2020\ActivityLog\Http\Controllers\ActivityLog;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\ActivityLogFilterRequest;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\SecureDelete;


/**
 * Class ActivityLogController
 * @package Satis2020\ActivityLog\Http\Controllers\ActivityLog
 */
class ActivityLogController extends ApiController
{
    use SecureDelete;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:activity-log')->only(['index']);
    }


    /**
     * @param ActivityLogFilterRequest $request
     * @param ActivityLogService $activityLogService
     * @return Application|ResponseFactory|Response
     * @throws RetrieveDataUserNatureException
     */
    public function index(ActivityLogFilterRequest $request, ActivityLogService $activityLogService)
    {
        return response($activityLogService->allActivityFilters($this->institution()->id, $request, 200));
    }

    /***
     * @param ActivityLogService $activityLogService
     * @return Application|ResponseFactory|Response
     * @throws RetrieveDataUserNatureException
     */
    public function create(ActivityLogService $activityLogService)
    {
        return response([
            "filters" => $activityLogService->getDataForFiltering($this->institution()->id),
            "logs" => $activityLogService->allActivityFilters($this->institution()->id, null,200)
        ]);
    }

}
