<?php

namespace Satis2020\UemoaReportsWithoutClient\Http\Controllers\DataFilter;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Exports\UemoaReports\StateReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Relationship;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\UemoaReports;


/**
 * Class DataFilterController
 * @package Satis2020\UemoaReportsWithoutClient\Http\Controllers\DataFilter
 */
class DataFilterController extends ApiController
{
    use UemoaReports;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-any-institution')->only(['index', 'excelExport']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index()
    {

        return response()->json([
            'institutions' => Institution::whereHas('institutionType', function ($q){

                $q->where('maximum_number_of_institutions', 0);

            })->get(),
            'categories' => ClaimCategory::with('claimObjects')->get(),
            'requestChannels' => Channel::all(),
            'relationShip' => Relationship::all(),
            'functionTreating' => Unit::whereHas('unitType', function ($q){

                $q->where('can_treat', 1);

            })->get(),
            'status' => $this->allStatus()
        ], 200);

    }

}
