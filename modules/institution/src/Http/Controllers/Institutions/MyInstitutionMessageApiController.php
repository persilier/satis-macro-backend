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

class MyInstitutionMessageApiController extends ApiController
{
    use \Satis2020\ServicePackage\Traits\MessageApi;

    private $activityLogService;

    public function __construct( ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-my-institution-message-api')->only(['create', 'store']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Edit the form for creating a new resource.
     * @param Institution $institution
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function create()
    {
        $institution = $this->institution();
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
    public function store(Request $request)
    {
        $rules = [
            'message_api_id' => 'required|exists:message_apis,id',
            'params' => 'array'];

        $this->validate($request, $this->getRules($rules, $request));

        $institution = $this->institution();

        $params = $request->filled('params')? Arr::except($request->params, ['to', 'text']):[];
        $institutionMessageApi = InstitutionMessageApi::updateOrCreate(
            ['institution_id' => $institution->id],
            ['message_api_id' => $request->message_api_id, 'params' => $params]);

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
