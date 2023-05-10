<?php

namespace Satis2020\GlobalReport\Http\Controllers;

use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Traits\UnitTrait;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\Reporting\GlobalReportRequest;
use Satis2020\ServicePackage\Services\Reporting\GlobalReportService;


class AnyGlobalReportController extends ApiController
{
    use UnitTrait;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        //$this->middleware('permission:any-list-global-reporting')->only(['index', 'create']);
    }

    public function index(GlobalReportRequest $request, GlobalReportService $service)
    {
        $globalReport = null;
        if ($request->has('institutions') && count($request->institutions) > 0) {
            $datas = [];
            foreach ($request->institutions as $key => $value) {
                $request->merge([
                    "institution_id" => $value
                ]);
                $globalReport[$value] =  $service->GlobalReport($request);
            }
            foreach ($globalReport as  $institution_key => $institution) {
                foreach ($institution as $rapport_key => $rapport) {
                    if ($rapport_key != 'title' && $rapport_key != 'description') {
                        $datas[$rapport_key] = $datas[$rapport_key] ??  [];
                        array_push($datas[$rapport_key], [
                            "UnitId" => $institution_key,
                            "Unit" => Institution::find($institution_key)->name,
                            "total" => $rapport["total"] ?? 0,
                            "taux" => $rapport["taux"] ?? 0,
                        ]);
                    }
                }
            }
            $globalReport = $datas;
        } else {
            $globalReport = $service->GlobalReport($request);
        }
        return response()->json($globalReport, 200);
    }

    public function create()
    {
        return response()->json(Institution::all(), 200);
    }
}
