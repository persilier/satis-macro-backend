<?php

namespace Satis2020\Configuration\Http\Controllers\Mail;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Rules\SmtpParametersRules;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class MailController extends ApiController
{

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:show-mail-parameters')->only(['show']);
        $this->middleware('permission:update-mail-parameters')->only(['update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $parameters = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'mail-parameters')->first()->data))->except(['password']);
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
        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'mail-parameters')->first()->data);

        if (!$request->has('password')) {

            $request->merge(['password' => $parameters->password]);

        }

        $request->merge(['security' => strtolower($request->security)]);

        $rules = [
            'senderID' => 'required',
            'username' => 'required',
            'password' => ['min:2', 'required'],
            'from' => 'required',
            'server' => ['required', new SmtpParametersRules($request->all())],
            'port' => 'integer|required',
            'security' => ['required', Rule::in(['ssl', 'tls'])]
        ];

        $this->validate($request, $rules);

        $request->merge(['state' => 1]);

        $new_parameters = $request->only(['senderID', 'username', 'password', 'from', 'server', 'port', 'security', 'state']);

        $metadata = Metadata::where('name', 'mail-parameters')->first()->update(['data' => json_encode
        ($new_parameters)]);

        $this->activityLogService->store('Configuration des paramètres smtp pour l\'envoie de mail',
            $this->institution()->id,
            'metadata',
            $this->activityLogService::UPDATED,
            $this->user(), $metadata
        );

        return response()->json($new_parameters, 200);
    }

}