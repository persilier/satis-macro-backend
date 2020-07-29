<?php

namespace Satis2020\ReportingClaimMyInstitution\Http\Controllers\Export;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ClaimController
 * @package Satis2020\ReportingClaimMyInstitution\Http\Controllers\Reporting
 */
class ExportController extends ApiController
{
    use ReportingClaim;
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-my-institution')->only(['index']);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function pdfExport(Request $request)
    {
        $rules = [
            'data_export' => 'required|json'
        ];

        $this->validate($request, $rules);

        $institution = $this->institution();

        $data = $request->data_export;

        $lang = app()->getLocale();

        $data = view('ServicePackage::reporting.pdf',$this->dataPdf($data, $lang, $institution, true))->render();

        $file = 'Reporting_'.time().'.pdf';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($data);
        return $pdf->download($file);
    }


}
