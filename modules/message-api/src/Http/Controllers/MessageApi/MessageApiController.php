<?php

namespace Satis2020\MessageApi\Http\Controllers\MessageApi;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\MessageApi;
use Satis2020\ServicePackage\Rules\MessageApiMethodRules;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class MessageApiController extends ApiController
{

    use \Satis2020\ServicePackage\Traits\MessageApi;

    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-message-apis')->only(['index']);
        $this->middleware('permission:store-message-apis')->only(['store', 'create']);
        $this->middleware('permission:update-message-apis')->only(['update', 'edit']);
        $this->middleware('permission:destroy-message-apis')->only(['destroy']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return response()->json(MessageApi::all(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json([
            'methods' => $this->getMethods()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     * @throws \ReflectionException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function store(Request $request)
    {

        $rules = [
            'name' => "required|unique:message_apis,name,NULL,NULL,deleted_at,NULL",
            'method' => ['required', new MessageApiMethodRules()]
        ];

        $this->validate($request, $rules);

        $request->merge(['params' => $this->getParameters($request->get('method'))]);

        $messageApi = MessageApi::create($request->all());

        $this->activityLogService->store("Enregistrement d'une Api de message",
            $this->institution()->id,
            $this->activityLogService::CREATED,
            'message_api',
            $this->user(),
            $messageApi
        );

        return response()->json($messageApi, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param MessageApi $discussion
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(MessageApi $discussion)
    {

    }

    /**
     * Edit the form for creating a new resource.
     * @param MessageApi $messageApi
     * @return \Illuminate\Http\Response
     */
    public function edit(MessageApi $messageApi)
    {
        return response()->json([
            'messageApi' => $messageApi,
            'methods' => $this->getMethods($messageApi->method)
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param MessageApi $messageApi
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function update(Request $request, MessageApi $messageApi)
    {
        $rules = [
            'name' => "required|unique:message_apis,name,{$messageApi->id},id,deleted_at,NULL",
            'method' => ['required', new MessageApiMethodRules($messageApi->method)]
        ];

        $this->validate($request, $rules);

        $request->merge(['params' => $this->getParameters($request->get('method'))]);

        $messageApi->update($request->all());

        return response()->json($messageApi, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param MessageApi $messageApi
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, MessageApi $messageApi)
    {
        $messageApi->secureDelete();

        return response()->json($messageApi, 200);
    }
}
