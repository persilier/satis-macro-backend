<?php

namespace Satis2020\Configuration\Http\Controllers\Mail;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Rules\SmtpParametersRules;

class MailController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:show-mail-parameters')->only(['show']);
        $this->middleware('permission:update-mail-parameters')->only(['update']);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $parameters = collect(json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'mail-parameters')->first()->data))->except(['password']);
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

        $parameters = json_decode(\Satis2020\ServicePackage\Models\Metadata::where('name', 'mail-parameters')->first()->data);

        $rules = [
            'senderID' => 'required',
            'username' => 'required',
            'password' => [
                'min:2',
                Rule::requiredIf(function () use ($parameters) {
                    return !property_exists($parameters, 'password');
                })],
            'from' => 'required',
            'server' => ['required', new SmtpParametersRules($request->all())],
            'port' => 'integer|required',
            'security' => ['required', Rule::in(['ssl', 'tls'])]
        ];

        $this->validate($request, $rules);

        $new_parameters = $request->only(['senderID', 'username', 'password', 'from', 'server', 'port', 'security']);

        Metadata::where('name', 'mail-parameters')->first()->update(['data' => json_encode($new_parameters)]);

        return response()->json($new_parameters, 200);
    }

}