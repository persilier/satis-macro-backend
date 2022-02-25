<?php

namespace Satis2020\Channel\Http\Controllers\Channels;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;

class ChannelToggleIsResponseController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:update-channel')->only(['update']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Channel $channel
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Channel $channel)
    {
        if (!$channel->is_editable && $channel->can_be_response && $channel->is_response) {
            $channel->update(['is_response' => 0]);
        } elseif (!$channel->is_editable && $channel->can_be_response && !$channel->is_response) {
            $channel->update(['is_response' => 1]);
        }

        return response()->json($channel, 201);
    }

}
