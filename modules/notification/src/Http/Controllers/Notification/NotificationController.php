<?php

namespace Satis2020\Notification\Http\Controllers\Notification;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

class NotificationController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');

        $this->middleware('permission:update-notifications')->only(['edit', 'update']);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit()
    {
        return response()->json(json_decode(Metadata::ofName('notifications')->firstOrFail()->data), 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'notifications.acknowledgment-of-receipt' => 'required|string',
            'notifications.register-a-claim' => 'required|string',
            'notifications.complete-a-claim' => 'required|string',
            'notifications.transferred-to-targeted-institution' => 'required|string',
            'notifications.transferred-to-unit' => 'required|string',
            'notifications.assigned-to-staff' => 'required|string',
            'notifications.reject-a-claim' => 'required|string',
            'notifications.treat-a-claim' => 'required|string',
            'notifications.invalidate-a-treatment' => 'required|string',
            'notifications.validate-a-treatment' => 'required|string',
            'notifications.communicate-the-solution' => 'required|string',
            'notifications.communicate-the-solution-unfounded' => 'required|string',
            'notifications.add-contributor-to-discussion' => 'required|string',
            'notifications.post-discussion-message' => 'required|string',
            'notifications.transferred-to-unit-escalation' => 'required|string',
            'notifications.treat-a-claim-escalation' => 'required|string'
        ];

        $this->validate($request, $rules);

        $data = collect(json_decode(Metadata::ofName('notifications')->firstOrFail()->data))->map(function ($item, $key) use ($request) {
            $item->text = $request->notifications[$item->event];
            return $item;
        })->all();

        Metadata::where('name', 'notifications')->first()->update(['data' => json_encode($data)]);

        return response()->json($data, 201);
    }


}
