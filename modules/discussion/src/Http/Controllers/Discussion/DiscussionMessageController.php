<?php

namespace Satis2020\Discussion\Http\Controllers\Discussion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Notifications\PostDiscussionMessage;
use Satis2020\ServicePackage\Rules\MessageBelongsToDiscussionRules;
use Satis2020\ServicePackage\Rules\MessageIsPostedByStaffRules;
use Satis2020\ServicePackage\Rules\StaffBelongsToDiscussionContributorsRules;

class DiscussionMessageController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware(['permission:contribute-discussion'])->only(['index', 'store', 'destroy']);
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

        $discussion->load('messages');

        return response()->json(Message::with('parent.postedBy.identite', 'files', 'postedBy.identite')
            ->where('discussion_id', $discussion->id)
            ->orderByDesc('created_at')
            ->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function create()
    {
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

        $request->merge(['posted_by' => $this->staff()->id]);

        $request->merge(['discussion_id' => $discussion->id]);

        $rules = [
            'text' => Rule::requiredIf(!$request->hasfile('files')),
            'posted_by' => ['required', 'exists:staff,id', new StaffBelongsToDiscussionContributorsRules($discussion)],
            'discussion_id' => 'required|exists:discussions,id',
            'files.*' => 'mimes:doc,pdf,docx,txt,jpeg,bmp,png,xls,xlsx,csv',
            'parent_id' => ['exists:messages,id', new MessageBelongsToDiscussionRules($discussion)]
        ];

        $this->validate($request, $rules);

        $message = Message::create($request->all());

        if ($request->hasfile('files')) {

            foreach ($request->file('files') as $file) {

                $title = $file->getClientOriginalName();
                $path = $file->store('discussions-files', 'public');

                // insert the file into database
                $message->files()->create(['title' => $title, 'url' => Storage::url($path)]);
            }
        }

        Notification::send(
            $this->getStaffIdentities($discussion->staff->pluck('id')->all(), [$this->staff()->id]),
            new PostDiscussionMessage($message)
        );

        $message->load(['files']);

        return response()->json($message, 200);
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
     * @param Message $message
     * @return \Illuminate\Http\JsonResponse $discussion
     * @throws \Exception
     */
    public function destroy(Request $request, Discussion $discussion, Message $message)
    {
        $request->merge(['message' => $message->id]);

        $request->merge(['staff' => $this->staff()->id]);

        $discussion->load('staff.identite', 'createdBy');

        $rules = [
            'message' => ['required', 'exists:messages,id', new MessageBelongsToDiscussionRules($discussion)],
            'staff' => [
                'required', 'exists:staff,id', new MessageIsPostedByStaffRules($message),
                new StaffBelongsToDiscussionContributorsRules($discussion)
            ],
        ];

        $this->validate($request, $rules);

        $message->delete();

        return response()->json($message, 200);
    }
}
