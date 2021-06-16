<?php

namespace Satis2020\Channel\Http\Controllers\Channels;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

class ResponseChannelController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-channel')->only(['index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return response()->json(Channel::where('is_response', true)->get(), 200);
    }

}
