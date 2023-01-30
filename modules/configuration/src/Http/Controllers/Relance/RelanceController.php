<?php

namespace Satis2020\Configuration\Http\Controllers\Relance;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

/**
 * Class RelanceController
 * @package Satis2020\Configuration\Http\Controllers\Relance
 */
class RelanceController extends ApiController
{
    protected $activityLogService;
    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:update-relance-parameters')->only(['show', 'update']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show()
    {
        return response()->json(
            [
                "coef" => json_decode(Metadata::query()->where('name', 'coef-relance')->firstOrFail()->data),
                "limit" => json_decode(Metadata::query()->where('name', Metadata::REGULATORY_LIMIT)->firstOrFail()->data),
                "domaine_prefixe" => json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'coef-relance-domaine-prefixe')->firstOrFail()->data),

            ],
            200
        );
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'coef' => 'required|integer',
            'domaine_prefixe' => 'present|array',
        ];

        $this->validate($request, $rules);

        $metadata = Metadata::where('name', 'coef-relance')->firstOrFail();
        $metadata->update(['data' => json_encode($request->coef)]);

        Metadata::where('name', 'coef-relance-domaine-prefixe')
            ->firstOrFail()->update(['data' => json_encode($request->domaine_prefixe)]);

        $this->activityLogService->store(
            'Configuration du coefficient applicable pour l\'envoie de relance',
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'metadata',
            $this->user(),
            $metadata
        );

        return response()->json($request->only('coef'), 200);
    }
}
