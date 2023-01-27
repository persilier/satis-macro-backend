<?php

namespace Satis2020\Configuration\Http\Controllers\Discussion;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class AllowPilotCollectorToDiscussionController extends ApiController
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:configure-pilot-collector-discussion-attribute')->only(['update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $parameters = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'allow-pilot-collector-to-discussion')->first()->data));
        $parameters = [
            "canPilotsDisc" => (int) $parameters["allow_pilot"],
            "canCollectorsDisc" => (int) $parameters["allow_collector"],
        ];
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

        $this->validate($request, [
            'canPilotsDisc' => ['required', Rule::in([1, 0])],
            'canCollectorsDisc' => ['required', Rule::in([1, 0])],
        ]);

        $metadata = Metadata::where('name', 'allow-pilot-collector-to-discussion')->first();
        $metadata->update([
            'data' => json_encode([
                "allow_pilot" => $request->canPilotsDisc,
                "allow_collector" => $request->canCollectorsDisc
            ])
        ]);

        $this->activityLogService->store(
            'Configuration des attributs des pilotes et des collecteurs',
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'metadata',
            $this->user(),
            $metadata
        );

        return response()->json(json_decode($metadata->data), 200);
    }
}
