<?php

namespace Satis2020\Configuration\Http\Controllers\SatisfactionData;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\Metadata as MetadataTrait;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class SatisfactionDataController extends ApiController
{
    use MetadataTrait;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
        $this->middleware('auth');
        $this->middleware('permission:update-satisfaction-data-config')->only(['update']);
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show()
    {
        return response()->json($this->getMetadataByName('satisfaction-data-config'), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'actived' => 'required|integer',
            'api_key' => 'required_if:actived,1',
        ];
        $this->validate($request, $rules);


        $data = $this->getMetadataByName('satisfaction-data-config');
        $metadata =  Metadata::where('name', 'satisfaction-data-config')->first();

        $metadata->update([
            'data' => json_encode([
                'actived' => $request->actived ?? $data->actived,
                'api_key' => $request->api_key ?? $data->api_key
            ])
        ]);

        $this->activityLogService->store(
            'Configuration du coefficient applicable pour l\'envoie de relance',
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'metadata',
            $this->user(),
            $metadata
        );

        return response()->json($metadata, 200);
    }
}
