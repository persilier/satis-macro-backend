<?php

namespace Satis2020\UsefulDataForBackoffice\Http\Controllers\RetrieveDataForCreateClaim;

use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Institution;

class RetrieveDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('client.credentials');
    }


    public function create()
    {
        return response()->json([
            "institutions" => Institution::with([
                'units.unitType',
                'defaultCurrency',
                'institutionType',
                'institutionMessageApi',
                'emailClaimConfiguration'
            ])->get(),
            "categories" => ClaimCategory::with('claimObjects')->get(),
            "currencies" => Currency::all(),
            "channels" => Channel::where('is_response', 1)->get()
        ], 200);
    }

}
