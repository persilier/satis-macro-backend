<?php

namespace Satis2020\Institution\Http\Controllers\Institutions;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionMessageApi;
use Satis2020\ServicePackage\Models\MessageApi;

class InstitutionMessageApiController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:update-institution-message-api')->only(['create','store']);
    }

    /**
     * Edit the form for creating a new resource.
     * @param Institution $institution
     * @return \Illuminate\Http\Response
     */
    public function create(Institution $institution)
    {
        if($institution->institutionType->name == "membre"){
            return response()->json([
                'error' => ['message' => 'Institution has not to be a member'],
            ], 409);
        }

        $institution->load(['institutionMessageApi.messageApi']);
        return response()->json([
            'institutionMessageApi' => $institution->institutionMessageApi,
            'messageApis' => MessageApi::all()
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Institution $institution
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request, Institution $institution)
    {

        if($institution->institutionType->name == "membre"){
            return response()->json([
                'error' => ['message' => 'Institution has not to be a member'],
            ], 409);
        }

        $messageApi = MessageApi::findOrFail($request->message_api_id);

        $rules = ['message_api_id' => 'required|exists:message_apis,id', 'params' => 'required|array'];

        foreach ($messageApi->params as $param){
            $rules['params.'.$param] = 'required';
        }

        $rulesFiltered = Arr::except($rules, ['params.to', 'params.text']);

        $this->validate($request, $rulesFiltered);

        $institutionMessageApi = InstitutionMessageApi::updateOrCreate(
            ['institution_id' => $institution->id],
            ['message_api_id' => $request->message_api_id, 'params' => Arr::except($request->params, ['to', 'text'])]
        );

        return response()->json($institutionMessageApi, 201);
    }


}
