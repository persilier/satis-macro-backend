<?php

namespace Satis2020\Channel\Http\Controllers\Channels;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;
use Illuminate\Support\Str;

class ChannelController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-channel')->only(['index']);
        $this->middleware('permission:store-channel')->only(['store']);
        $this->middleware('permission:show-channel')->only(['show']);
        $this->middleware('permission:update-channel')->only(['update']);
        $this->middleware('permission:destroy-channel')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return response()->json(Channel::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $request->replace([
            'name' => Str::upper($request->name)
        ]);
        $request->merge([
            'is_response' => false,
        ]);

        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('channels', 'name')],
            'is_response' => 'required|boolean',
        ];
        $this->validate($request, $rules);
        $channels = Channel::create($request->only(['name', 'is_response']));
        return response()->json($channels, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param Channel $channel
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Channel $channel)
    {
        return response()->json($channel, 200);
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
        $request->replace([
            'name' => Str::upper($request->name)
        ]);
        $request->merge(['is_response' => false]);

        $rules = [
            'name' => ['required', new TranslatableFieldUnicityRules('channels', 'name', 'id', "{$channel->id}")],
            'is_response' => 'required|boolean',
        ];

        $this->validate($request, $rules);

        if($channel->is_editable === 0)
            return $this->errorResponse('Ce cannal ne peut être modifier.', 400);

        $channel->slug = null;
        $channel->update(['name'=> $request->name, 'is_response'=> $request->is_response]);
        return response()->json($channel, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Channel $channel
     * @return $channel
     * @throws \Exception
     */
    public function destroy(Channel $channel)
    {
        if($channel->is_editable === 0)
            return $this->errorResponse('Ce cannal ne peut être supprimer.', 400);
        $channel->delete();
        return response()->json($channel, 200);
    }
}
