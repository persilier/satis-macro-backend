<?php

namespace Satis2020\Channel\Http\Controllers\Channels;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;

class MobileChannelController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:show-channel')->only(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return response()->json(Channel::where('slug', 'mobile')->firstOrFail(), 200);
    }

}
