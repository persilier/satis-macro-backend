<?php

namespace Satis2020\Institution\Http\Controllers\Institutions;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionMessageApi;
use Satis2020\ServicePackage\Models\MessageApi;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class InstitutionMessageApiController extends ApiController
{

    use \Satis2020\ServicePackage\Traits\MessageApi;

    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-institution-message-api')->only(['create', 'store']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Edit the form for creating a new resource.
     * @param Institution $institution
     * @return \Illuminate\Http\Response
     */
    public function create(Institution $institution)
    {
        $institution->load(['institutionMessageApi.messageApi']);
        return response()->json([
            'institutionMessageApi' => $institution->institutionMessageApi,
            'messageApis' => MessageApi::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Institution $institution
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request, Institution $institution)
    {
        $rules = ['message_api_id' => 'required|exists:message_apis,id', 'params' => 'required|array'];

        $this->validate($request, $this->getRules($rules, $request));

        $institutionMessageApi = InstitutionMessageApi::updateOrCreate(
            ['institution_id' => $institution->id],
            ['message_api_id' => $request->message_api_id, 'params' => Arr::except($request->params, ['to', 'text'])]
        );

        $this->activityLogService->store("Enregistrement d'une Api de message",
            $this->institution()->id,
            $this->activityLogService::CREATED,
            'message_api',
            $this->user(),
            $institutionMessageApi
        );

        return response()->json($institutionMessageApi, 201);
    }


}
