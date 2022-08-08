<?php

namespace Satis2020\Webhooks\Http\Controllers\Config;

use Dotenv\Exception\ValidationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\Webhooks\Consts\Event;
use Satis2020\Webhooks\Models\Webhook;
use Satis2020\Webhooks\Requests\WebhookConfigRequest;
use Satis2020\Webhooks\Rules\UniqueWebhookRule;
use Satis2020\Webhooks\Services\WebhookConfigService;
use Satis2020\Webhooks\Traits\ValidateRequestTrait;

class WebhooksConfigController extends ApiController
{

    use ValidateRequestTrait;

    /**
     * @var WebhookConfigService
     */
    private $webhookConfigServiceService;

    /**
     * WebhookConfigController constructor.
     * @param WebhookConfigService $webhookConfigServiceService
     */
    public function __construct(WebhookConfigService $webhookConfigServiceService)
    {
        parent::__construct();
        $this->webhookConfigServiceService = $webhookConfigServiceService;
        $this->middleware('auth:api');
        $this->middleware('permission:list-webhooks-config')->only(['index']);
        $this->middleware('permission:store-webhooks-config')->only(['create','store']);
        $this->middleware('permission:update-webhooks-config')->only(['edit','update']);
        $this->middleware('permission:delete-webhooks-config')->only(['destroy']);
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function index()
    {
        return response($this->webhookConfigServiceService->getAll());
    }

    public function create()
    {
        return response([
            "events"=>Event::getEvents()
        ]);
    }

    public function edit($webhookId,WebhookConfigService $configService)
    {
        return response([
            "webhook"=>$configService->getById($webhookId),
            "events"=>Event::getEvents()
        ]);
    }

    /**
     * @param WebhookConfigRequest $request
     * @return Application|ResponseFactory|Response
     */
    public function store(Request $request)
    {
        if ($request->isNotFilled('institution_id')){
            $request->merge(['institution_id'=>$this->institution()->id]);
        }

        $validator = Validator::make($request->all(),[
            'name' => ["string",'required','unique:webhooks,name'],
            'url' => ["required","url",'unique:webhooks,name'],
            'institution_id' => ['required','exists:institutions,id'],
            'event' => ['required','string',Rule::in(Event::getEventsValues()),new UniqueWebhookRule($request)],
        ]);

        if ($validator->fails()){
            return response($validator->errors(),422);
        }

        $config = $this->webhookConfigServiceService->store($request);

        return response($config,Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param $webhookId
     * @return Application|ResponseFactory|Response
     * @throws RetrieveDataUserNatureException
     */
    public function update(Request $request,$webhookId)
    {

        if ($request->isNotFilled('institution_id')){
            $request->merge(['institution_id'=>$this->institution()->id]);
        }

        $validator = Validator::make($request->all(),[
            'id' => ['required','exists:webhooks,id'],
            'name' => ["string",'required',Rule::unique('webhooks','name')->ignore($request->id)],
            'url' => ["required","url",],
            'institution_id' => ['required','exists:institutions,id'],
            'event' => ['required','string',Rule::in(Event::getEventsValues()),new UniqueWebhookRule($request)],
        ]);

        if ($validator->fails()){
            return response($validator->errors(),422);
        }

        $config = $this->webhookConfigServiceService->update($request);

        return response($config,Response::HTTP_OK);
    }

    /**
     * @param $webhookId
     * @return Application|ResponseFactory|Response
     */
    public function destroy($webhookId)
    {
        return response($this->webhookConfigServiceService->remove($webhookId));
    }



}