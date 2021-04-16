<?php

namespace Satis2020\ReportingClaimAnyInstitution\Http\Controllers\Export;

use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ClaimController
 * @package Satis2020\ReportingClaimAnyInstitution\Http\Controllers\Reporting
 */
class ExportController extends ApiController
{
    use ReportingClaim;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-any-institution')->only(['index']);
    }


    /**
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function pdfExport(Request $request)
    {
        $rules = [

            'data_export' => 'required'
        ];

        $this->validate($request, $rules);

        $institution = $this->institution();

        $data = $request->data_export;

        $lang = app()->getLocale();

        $data = view('ServicePackage::reporting.pdf',$this->dataPdf($data, $lang, $institution))->render();
        
        $file = 'Reporting_'.time().'.pdf';

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($data);

        return $pdf->download($file);
    }


}
