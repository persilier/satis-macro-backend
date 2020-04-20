<?php

namespace Satis2020\ConfigurationsPackage\Http\Controllers\Mail;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;

class MailController extends ApiController
{

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
            'password' => 'min:2',
            'from' => 'required',
            'server' => 'required',
            'port' => 'integer|required',
            'security' => ['required', Rule::in(['ssl', 'tls'])]
        ];

        $this->validate($request, $rules);

        if (is_null($parameters->password)) {
            $this->errorResponse('password is required.', 204);
        }

        $new_parameters = $request->only(['senderID', 'username', 'password', 'from', 'server', 'port', 'security']);
        
        Metadata::where('name', 'mail-parameters')->first()->update(['data'=> json_encode($new_parameters)]);

        return response()->json($new_parameters, 200);
    }

}