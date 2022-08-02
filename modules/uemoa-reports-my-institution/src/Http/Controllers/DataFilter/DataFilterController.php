<?php

namespace Satis2020\UemoaReportsMyInstitution\Http\Controllers\DataFilter;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\UemoaReports;


/**
 * Class DataFilterController
 * @package Satis2020\UemoaReportsAnyInstitution\Http\Controllers\DataFilter
 */
class DataFilterController extends ApiController
{
    use UemoaReports;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-my-institution')->only(['index', 'excelExport']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $instittion = $this->institution();

        return response()->json([
            'categories' => ClaimCategory::with('claimObjects')->get(),
            'requestChannels' => Channel::all(),
            'clientTypes' => AccountType::all(),
            'agences' => Unit::where('institution_id', $instittion->id)->whereHas('unitType', function ($q){
                $q->where('can_be_target', 1);
            })->get(),

            'functionTreating' => Unit::where('institution_id', $instittion->id)->whereHas('unitType', function ($q){
                $q->where('can_treat', 1);
            })->get(),
            'status' => $this->allStatus()

        ], 200);

    }

}
