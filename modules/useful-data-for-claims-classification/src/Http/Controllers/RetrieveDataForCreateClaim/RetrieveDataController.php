<?php

namespace Satis2020\UsefulDataForBackoffice\Http\Controllers\RetrieveDataForCreateClaim;

use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;

class RetrieveDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('client.credentials');
    }


    public function create()
    {
        $unitTypes = UnitType::withTrashed()->get();
        return response()->json([
            "institutions" => Institution::withTrashed()->get(),
            "categories" => ClaimCategory::withTrashed()->get(),
            "objects" => ClaimObject::withTrashed()->get(),
            "categoryClients" => CategoryClient::withTrashed()->get(),
            "units" => Unit::withTrashed()->get()->map(function ($item) use ($unitTypes) {
                $item['can_be_target'] = $unitTypes->firstWhere('id', $item->unit_type_id)->can_be_target;
                return $item;
            }),
            "currencies" => Currency::withTrashed()->get(),
            "channels" => Channel::where('is_response', 1)->withTrashed()->get()
        ], 200);
    }

}
