<?php

namespace Satis2020\StaffFeedbackChannel\Http\Controllers\StaffFeedbackChannel;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Metadata;

class StaffFeedbackChannelController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
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
            'feedback_preferred_channels' => 'required|array',
            'feedback_preferred_channels.*' => ['required', Rule::in(Channel::where('is_response', true)->get()->pluck('slug')->all())],
        ];

        $this->validate($request, $rules);

        $staff = $this->staff();

        $staff->update(['feedback_preferred_channels' => $request->feedback_preferred_channels]);

        return response()->json($staff, 201);
    }


}
