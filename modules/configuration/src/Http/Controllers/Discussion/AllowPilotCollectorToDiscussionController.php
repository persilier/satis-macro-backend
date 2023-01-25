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

        //$this->middleware('permission:show-pilot-contributor-discussion-attribute')->only(['show']);
        //$this->middleware('permission:update-pilot-contributor-attribute-discussion-parameters')->only(['update']);

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
            "canPilotsDisc" => $parameters["allow-pilot"],
            "canCollectorsDisc" => $parameters["allow-collector"],
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

        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'allow-pilot-collector-to-discussion')->first()->data);

        $request->merge([
            "canPilotsDisc" => $request->boolean("canPilotsDisc"),
            "canCollectorsDisc" => $request->boolean("canCollectorsDisc"),
        ]);

        $rules = [
            'canPilotsDisc' => ['required', Rule::in([true, false])],
            'canCollectorsDisc' => ['required', Rule::in([true, false])],
        ];

        $this->validate($request, $rules);
        
        $new_parameters = $request->only(['canPilotsDisc', 'canCollectorsDisc']);
        
        $metadata = Metadata::where('name', 'allow-pilot-collector-to-discussion')->first()->update(['data'=> json_encode($new_parameters)]);

        $this->activityLogService->store('Configuration des attributs des pilotes et des collecteurs',
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'metadata',
            $this->user(), $metadata
        );

        return response()->json($new_parameters, 200);
    }

}