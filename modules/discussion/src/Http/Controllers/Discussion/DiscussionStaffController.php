<?php

namespace Satis2020\Discussion\Http\Controllers\Discussion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Notifications\AddContributorToDiscussion;
use Satis2020\ServicePackage\Rules\DiscussionIsRegisteredByStaffRules;
use Satis2020\ServicePackage\Rules\StaffBelongsToDiscussionContributorsRules;
use Satis2020\ServicePackage\Rules\StaffCanBeAddToDiscussionRules;
use Satis2020\ServicePackage\Rules\StaffIsNotDiscussionContributorRules;

class DiscussionStaffController extends ApiController
{

    use \Satis2020\ServicePackage\Traits\Discussion, \Satis2020\ServicePackage\Traits\Notification;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-discussion-contributors')->only(['index']);
        $this->middleware('permission:add-discussion-contributor')->only(['store', 'create']);
        $this->middleware('permission:remove-discussion-contributor')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Discussion $discussion
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */

    public function index(Request $request, Discussion $discussion)
    {

        $request->merge(['staff' => $this->staff()->id]);

        $rules = [
            'staff' => ['required', 'exists:staff,id', new StaffBelongsToDiscussionContributorsRules($discussion)]
        ];

        $this->validate($request, $rules);

        $discussion->load(['staff.identite', 'createdBy.identite']);

        return response()->json($discussion, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param Discussion $discussion
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function create(Request $request, Discussion $discussion)
    {
        $request->merge(['staff' => $this->staff()->id]);

        $rules = [
            'staff' => ['required', 'exists:staff,id', new StaffBelongsToDiscussionContributorsRules($discussion)]
        ];

        $this->validate($request, $rules);

        $discussion->load('staff.identite', 'createdBy.unit');

        return response()->json([
            'staff' => $this->getContributors($discussion),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Discussion $discussion
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request, Discussion $discussion)
    {
        $discussion->load('staff.identite', 'createdBy.unit');

        $request->merge(['discussion' => $discussion->id]);

        $rules = [
            'discussion' => ['required', 'exists:discussions,id',
                new DiscussionIsRegisteredByStaffRules($discussion, $this->staff())],
            'staff_id' => 'required|array',
            'staff_id.*' => ['required', 'exists:staff,id', new StaffIsNotDiscussionContributorRules($discussion),
                new StaffCanBeAddToDiscussionRules($discussion)],
        ];

        $this->validate($request, $rules);

        $discussion->staff()->attach($request->staff_id);

        Notification::send($this->getStaffIdentities($request->staff_id), new AddContributorToDiscussion($discussion));

        return response()->json($discussion->staff, 201);
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
     * @param Staff $staff
     * @return \Illuminate\Http\JsonResponse $discussion
     * @throws ValidationException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function destroy(Request $request, Discussion $discussion, Staff $staff)
    {
        $request->merge(['discussion' => $discussion->id]);

        $request->merge(['staff' => $staff->id]);

        $discussion->load('staff.identite', 'createdBy');

        $rules = [
            'discussion' => ['required', 'exists:discussions,id', new DiscussionIsRegisteredByStaffRules($discussion, $this->staff())],
            'staff' => ['required', 'exists:staff,id', new StaffBelongsToDiscussionContributorsRules($discussion)],
        ];

        $this->validate($request, $rules);

        $discussion->staff()->detach($staff->id);

        return response()->json($discussion, 200);
    }
}
