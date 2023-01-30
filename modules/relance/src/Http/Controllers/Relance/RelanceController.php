<?php

namespace Satis2020\Relance\Http\Controllers\Relance;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Jobs\RelanceMail;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\RelanceTrait;


/**
 * Class RelanceController
 * @package Satis2020\Relance\Http\Controllers\Relance
 */
class RelanceController extends ApiController
{
    use RelanceTrait;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:any-send-relance')->only(['sendAnyRelance']);
        $this->middleware('permission:my-send-relance')->only(['sendMyRelance']);
    }


    /**
     * @param Request $request
     * @param Claim $claim
     * @return JsonResponse
     * @throws ValidationException
     */
    public function sendAnyRelance(Request $request, Claim $claim){

        $this->validate($request, $this->rulesAnyMyRelanceSend());
        $my = false;
        RelanceMail::dispatch($request, $claim, $my);
        return response()->json($claim, 200);
    }


    /**
     * @param Request $request
     * @param Claim $claim
     * @return JsonResponse
     * @throws ValidationException
     */
    public function sendMyRelance(Request $request, Claim $claim){

        $this->validate($request, $this->rulesAnyMyRelanceSend());
        $my = true;
        RelanceMail::dispatch($request, $claim, $my);
        return response()->json($claim, 200);
    }

}
