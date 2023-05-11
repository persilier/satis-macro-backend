<?php

namespace Satis2020\GlobalReport\Http\Controllers;

use Facade\Ignition\DumpRecorder\Dump;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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
                $title = $institution['title'];
                $description = $institution['description'];
                foreach ($institution as $rapport_key => $rapport) {

                    if ($rapport_key != 'title' && $rapport_key != 'description') {
                        if (is_array($rapport)) {
                            $datas[$rapport_key] = $datas[$rapport_key] ??  [];
                            if (!in_array('total', $rapport) && !in_array('taux', $rapport)) {

                                $datas[$rapport_key] = array_merge($datas[$rapport_key], $rapport);
                            } else {
                                array_push(
                                    $datas[$rapport_key],
                                    !in_array('total', $rapport) && !in_array('taux', $rapport) ?
                                        array_merge($datas[$rapport_key], $rapport) :
                                        [
                                            "UnitId" => $institution_key,
                                            "Unit" => ["fr" => Institution::find($institution_key)->name],
                                            "total" => $rapport["total"] ?? 0,
                                            "taux" => $rapport["taux"] ?? 0,
                                        ]
                                );
                            }
                        } else {
                            $datas[$rapport_key] = $datas[$rapport_key] ??  [];
                            array_push($datas[$rapport_key], [
                                "UnitId" => $institution_key,
                                "Unit" => ["fr" => Institution::find($institution_key)->name],
                                "total" => $rapport,
                                "taux" => $rapport,
                            ]);
                        }
                    }
                }
            }
            $globalReport = $datas;
            $globalReport['title'] = $title;
            $globalReport['description'] = $description;
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
