<?php

namespace Satis2020\MobileApi\Http\Controllers\Statistique;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\MobileApi;

/**
 * Class StatistiqueController
 * @package Satis2020\MobileApi\Http\Controllers\Statistique
 */
class StatistiqueController extends ApiController
{
    use MobileApi;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

//        $this->middleware('permission:list-message-apis')->only(['index']);
//        $this->middleware('permission:store-message-apis')->only(['store', 'create']);
//        $this->middleware('permission:update-message-apis')->only(['update', 'edit']);
//        $this->middleware('permission:destroy-message-apis')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return void
     * @throws ValidationException
     */

    public function statistiques(Request $request)
    {

        $date_start = Carbon::parse($request->date_start)->startOfDay();
        $date_end = Carbon::parse($request->date_end)->endOfDay();

        $this->validate($request, $this->rules());

        return response()->json([

            'totalClaimReceived' => $this->totalClaimReceived($date_start, $date_end),
            'totalClaimTreated' => $this->totalClaimTreated($date_start, $date_end),
            'totalClaimUntreated' => $this->totalClaimUntreated($date_start, $date_end),
            'rateSatisfaction' => $this->rateSatisfaction($date_start, $date_end),
            'numberDaysMedium' => $this->numberDaysMediumProcessing($date_start, $date_end)

        ], 200);
    }


}
