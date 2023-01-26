<?php

namespace Satis2020\Discussion\Http\Controllers\Discussion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Rules\ClaimIsAssignedToStaffRules;
use Satis2020\ServicePackage\Rules\DiscussionIsRegisteredByStaffRules;

class DiscussionController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware(['permission:list-my-discussions', 'allow.pilot.collector.to.discussion:collector-filial-pro'])->only(['index']);
        $this->middleware(['permission:store-discussion', 'allow.pilot.collector.to.discussion:pilot'])->only(['store']);
        $this->middleware(['permission:destroy-discussion', 'allow.pilot.collector.to.discussion:pilot'])->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */

    public function index()
    {

        return response()->json(
            Staff::with('discussions.claim')
                ->findOrFail($this->staff()->id)
                ->discussions
                ->values(),
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {
        $request->merge(['created_by' => $this->staff()->id]);

        if ($this->staff()->is_active_pilot) {
            $allow_pilot_create_discussion = Config::get("services.allow_pilot_create_discussion");
            if ($allow_pilot_create_discussion == 1) {
                $rules = [
                    'name' => 'required',
                    'created_by' => 'required|exists:staff,id'
                ];
            } else {
                $rules = [
                    'name' => 'required',
                    'claim_id' => ['required', 'exists:claims,id'],
                    'created_by' => 'required|exists:staff,id'
                ];
            }
        } else {
            $rules = [
                'name' => 'required',
                'claim_id' => ['required', 'exists:claims,id', new ClaimIsAssignedToStaffRules($request->created_by)],
                'created_by' => 'required|exists:staff,id'
            ];
        }

        $this->validate($request, $rules);
        $discussion = Discussion::create($request->all());
        $discussion->staff()->attach($request->created_by);
        return response()->json($discussion, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Discussion $discussion
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Discussion $discussion)
    {
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Discussion $discussion
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Discussion $discussion)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Discussion $discussion
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     * @throws ValidationException
     */
    public function destroy(Request $request, Discussion $discussion)
    {
        $request->merge(['discussion' => $discussion->id]);

        $discussion->load('staff.identite', 'createdBy');

        $rules = [
            'discussion' => ['required', 'exists:discussions,id', new DiscussionIsRegisteredByStaffRules($discussion, $this->staff())]
        ];

        $this->validate($request, $rules);

        $discussion->delete();

        return response()->json($discussion, 200);
    }
}
