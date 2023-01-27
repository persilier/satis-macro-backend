<?php

namespace Satis2020\Discussion\Http\Controllers\Discussion;

use Illuminate\Http\Request;
<<<<<<< HEAD
use Satis2020\ServicePackage\Models\Staff;
=======
use Illuminate\Support\Arr;
>>>>>>> develop
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Rules\StaffCanBeAddToDiscussionRules;
use Satis2020\ServicePackage\Notifications\AddContributorToDiscussion;
use Satis2020\ServicePackage\Rules\DiscussionIsRegisteredByStaffRules;
<<<<<<< HEAD
use Satis2020\ServicePackage\Rules\StaffIsNotDiscussionContributorRules;
use Satis2020\ServicePackage\Rules\StaffBelongsToDiscussionContributorsRules;
=======
use Satis2020\ServicePackage\Rules\StaffBelongsToDiscussionContributorsRules;
use Satis2020\ServicePackage\Rules\StaffCanBeAddToDiscussionRules;
use Satis2020\ServicePackage\Rules\StaffCanBeAddToEscalationDiscussionRules;
use Satis2020\ServicePackage\Rules\StaffIsNotDiscussionContributorRules;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Satis2020\ServicePackage\Traits\ClaimTrait;
>>>>>>> develop

class DiscussionStaffController extends ApiController
{

<<<<<<< HEAD
    use \Satis2020\ServicePackage\Traits\Discussion, \Satis2020\ServicePackage\Traits\Notification, \Satis2020\ServicePackage\Traits\Metadata;
=======
    use \Satis2020\ServicePackage\Traits\Discussion, \Satis2020\ServicePackage\Traits\Notification,ClaimAwaitingTreatment;
>>>>>>> develop

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware(['permission:list-discussion-contributors', 'allow.pilot.collector.to.discussion:collector-filial-pro'])->only(['index']);
        $this->middleware(['permission:add-discussion-contributor',  'allow.pilot.collector.to.discussion:pilot'])->only(['store', 'create']);
        $this->middleware(['permission:remove-discussion-contributor',  'allow.pilot.collector.to.discussion:pilot'])->only(['destroy']);
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
     * @return \Illuminate\Http\JsonResponse
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

<<<<<<< HEAD
        $config = $this->getMetadataByName('allow-pilot-collector-to-discussion');

        return response()->json([
            'staff' => (int) $config->allow_collector === 1 ? $this->getContributorsWithClaimCreator($discussion) : $this->getContributors($discussion),
        ], 200);
=======

        if (isEscalationClaim($discussion->claim)){
            $response = [
                'staff' => $this->getContributors($discussion),
                "escalation_staff"=>$this->getTargetedStaffFromUnit($this->getNormalTreatment($discussion->claim_id)->responsible_unit_id)
            ];
        }else{
            $response = [
                'staff' => $this->getContributors($discussion),
            ];
        }

        return response()->json($response);
>>>>>>> develop
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
            'discussion' => [
                'required', 'exists:discussions,id',
                new DiscussionIsRegisteredByStaffRules($discussion, $this->staff())
            ],
            'staff_id' => 'required|array',
<<<<<<< HEAD
            'staff_id.*' => [
                'required', 'exists:staff,id', new StaffIsNotDiscussionContributorRules($discussion),
                new StaffCanBeAddToDiscussionRules($discussion)
            ],
=======
            'escalation_staff' => 'array',
            'staff_id.*' => ['required', 'exists:staff,id', new StaffIsNotDiscussionContributorRules($discussion), new StaffCanBeAddToDiscussionRules($discussion)],
            'escalation_staff.*' => [ 'exists:staff,id',  new StaffCanBeAddToEscalationDiscussionRules($discussion)],
>>>>>>> develop
        ];

        $this->validate($request, $rules);

        if ($request->filled('escalation_staff')){
            $staff = array_merge($request->staff_id,$request->escalation_staff);
        }else{
            $staff = $request->staff_id;
        }

        $discussion->staff()->attach($staff);

        Notification::send($this->getStaffIdentities($staff), new AddContributorToDiscussion($discussion));

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
