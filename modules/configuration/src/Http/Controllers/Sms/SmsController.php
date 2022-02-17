<?php

namespace Satis2020\Configuration\Http\Controllers\Sms;

use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

class SmsController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:show-sms-parameters')->only(['show']);
        $this->middleware('permission:update-sms-parameters')->only(['update']);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $parameters = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'sms-parameters')->first()->data))->except(['password']);
        return response()->json($parameters, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {

        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'sms-parameters')->first()->data);

        $rules = [
            'senderID' => 'required',
            'username' => 'required',
            'password' => 'min:2',
            'indicatif' => 'required',
            'api' => 'required'
        ];

        $this->validate($request, $rules);

        if (is_null($parameters->password)) {
            $this->errorResponse('password is required.', 204);
        }

        $new_parameters = $request->only(['senderID', 'username', 'password', 'indicatif', 'api']);
        
        Metadata::where('name', 'sms-parameters')->first()->update(['data'=> json_encode($new_parameters)]);

        return response()->json($new_parameters, 200);
    }

}