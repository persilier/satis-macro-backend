<?php

namespace Satis2020\StaffFeedbackChannel\Http\Controllers\StaffFeedbackChannel;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class StaffFeedbackChannelController extends ApiController
{

    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function edit()
    {
        $staff = $this->staff();
        return response()->json([
            'staff' => $staff,
            //'channels' => Channel::query()->where("can_be_response",true)->get()->pluck('slug')
            'channels' => Channel::query()->where('is_response',true)->get()->pluck('slug')
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function update(Request $request)
    {
        $rules = [
            'feedback_preferred_channels' => 'sometimes|array',
            'feedback_preferred_channels.*' => ['sometimes', Rule::in(Channel::where('is_response', true)->get()->pluck('slug')->all())],
        ];

        /*$rules = [
            'feedback_preferred_channels' => 'required|array',
            'feedback_preferred_channels.*' => ['required', Rule::in(Channel::where('is_response', true)->get()->pluck('slug')->all())],
        ];*/

        $this->validate($request, $rules);
        $feedback_preferred_channels = $request->feedback_preferred_channels!=[] ? $request->feedback_preferred_channels : null;
        $staff = $this->staff();
        $staff->update(['feedback_preferred_channels' => $feedback_preferred_channels]);

        $this->activityLogService->store("Mise à jour du canal de feedback d'un staff",
            $this->institution()->id,
            ActivityLogService::UPDATE_STAFF,
            'staff',
            $this->user(),
            $staff
        );

        return response()->json($staff, 201);
    }


}
