<?php

namespace Satis2020\Configuration\Http\Controllers\Sms;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class SmsController extends ApiController
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:show-sms-parameters')->only(['show']);
        $this->middleware('permission:update-sms-parameters')->only(['update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $parameters = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'sms-parameters')->first()->data))->except(['password']);
        return response()->json($parameters, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {

        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'sms-parameters')->first()->data);

        $rules = [
            'senderID' => 'required',
            'username' => 'required',
            'password' => 'min:2',
            'indicatif' => 'required',
            'api' => 'required'
        ];

        $this->validate($request, $rules);

        if (is_null($parameters->password)) {
            $this->errorResponse('password is required.', 204);
        }

        $new_parameters = $request->only(['senderID', 'username', 'password', 'indicatif', 'api']);
        
        $metadata = Metadata::where('name', 'sms-parameters')->first()->update(['data'=> json_encode($new_parameters)]);

        $this->activityLogService->store('Configuration sms pour l\'envoie de mail',
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'metadata',
            $this->user(), $metadata
        );

        return response()->json($new_parameters, 200);
    }

}