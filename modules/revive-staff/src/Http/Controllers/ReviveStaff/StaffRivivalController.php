<?php

namespace Satis2020\ReviveStaff\Http\Controllers\ReviveStaff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Notifications\ReviveStaff;
use Satis2020\ServicePackage\Services\RevivalService;
use Satis2020\ServicePackage\Services\StaffService;
use Symfony\Component\HttpFoundation\Response;

class StaffRivivalController extends ApiController
{

    use \Satis2020\ServicePackage\Traits\Notification;

    /**
     * @var StaffService
     */
    private $staffService;
    /**
     * @var RevivalService
     */
    private $revivalService;

    public function __construct(RevivalService $revivalService, StaffService $staffService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-staff-revivals')->only(['index']);

        $this->staffService = $staffService;
        $this->revivalService = $revivalService;

    }

    public function index(Request $request,$staffId=null)
    {
        $size = $request->get("size");
        $staffId = $staffId==null?$this->staff()->id:$staffId;

        return $this->revivalService->getStaffRevivals($staffId,$size);
    }

}
