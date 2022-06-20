<?php

namespace Satis2020\Escalation\Http\Controllers\TreatmentBoard;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\Escalation\Requests\TreatmentBoardRequest;
use Satis2020\Escalation\Services\TreatmentBoardService;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\StaffManagement;

class TreatmentBoardController extends ApiController
{
    use StaffManagement;

    /**
     * @var TreatmentBoardService
     */
    private $treatmentBordService;
    protected $activityLogService;

    /**
     * EscalationConfigController constructor.
     * @param TreatmentBoardService $treatmentBordService
     * @param ActivityLogService $activityLogService
     */
    public function __construct(TreatmentBoardService $treatmentBordService, ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->treatmentBordService = $treatmentBordService;
        $this->activityLogService = $activityLogService;
        $this->middleware('auth:api');
        $this->middleware('permission:list-treatment-board')->only(['index']);
        $this->middleware('permission:store-treatment-board')->only(['store']);
        $this->middleware('permission:update-treatment-board')->only(['update']);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function index(Request $request)
    {
        return response($this->treatmentBordService->getAll($request->size));
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function create()
    {
        return response([
            "staff"=> $this->getAllStaff()
        ]);
    }

    /**
     * @param TreatmentBoard $treatmentBoard
     * @param TreatmentBoardService $boardService
     * @return Application|ResponseFactory|Response
     */
    public function edit($treatmentBoardId,TreatmentBoardService $boardService)
    {
        return response([
            "staff"=> $this->getAllStaff(),
            "treatmentBoard"=>$boardService->getById($treatmentBoardId)
        ]);
    }

    /**
     * @param TreatmentBoardRequest $request
     * @return Application|ResponseFactory|Response
     * @throws RetrieveDataUserNatureException
     */
    public function store(TreatmentBoardRequest $request)
    {
        $request->merge(['institution_id'=>$this->institution()->id]);

        $this->activityLogService->store("Ajout de commités",
            $this->institution()->id,
            $this->activityLogService::CREATED,
            'treatment_board',
            $this->user()
        );
        return response($this->treatmentBordService->store($request),Response::HTTP_OK);
    }

    /**
     * @param TreatmentBoardRequest $request
     * @param $treatmentBoardId
     * @return Application|ResponseFactory|Response
     * @throws RetrieveDataUserNatureException
     */
    public function update(TreatmentBoardRequest $request,$treatmentBoardId)
    {
        $this->activityLogService->store("Mise à jour de commité",
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'treatment_board',
            $this->user()
        );
        return response($this->treatmentBordService->update($request,$treatmentBoardId),Response::HTTP_OK);
    }
}