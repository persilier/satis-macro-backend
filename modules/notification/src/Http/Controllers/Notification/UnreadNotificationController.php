<?php

namespace Satis2020\Notification\Http\Controllers\Notification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

class UnreadNotificationController extends ApiController
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
    public function index()
    {
        return response()->json(Auth::user()->identite->unreadNotifications, 200);
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
            'notifications' => 'required|array',
            'notifications.*' => 'required|exists:notifications,id'
        ];

        $this->validate($request, $rules);

        Auth::user()->identite
            ->notifications()
            ->whereIn('id', $request->notifications)
            ->get()
            ->markAsRead();

        return response()->json(Auth::user()->identite->notifications()->get(), 201);
    }


}
