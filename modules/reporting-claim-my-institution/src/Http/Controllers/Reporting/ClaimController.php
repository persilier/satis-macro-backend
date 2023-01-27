<?php

namespace Satis2020\ReportingClaimMyInstitution\Http\Controllers\Reporting;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ClaimController
 * @package Satis2020\ReportingClaimMyInstitution\Http\Controllers\Reporting
 */
class ClaimController extends ApiController
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
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $institution = $this->institution();

        $this->validate($request, $this->rules(false));

        if((!$request->has('date_start')) || (!$request->has('date_end'))){

            $request->merge(['date_start' => now()->startOfMonth()->subMonths(11), 'date_end' => now()->endOfMonth()]);
        }

        $request->merge(['institution_id' => $institution->id]);

        $statistiques = [
            'statistiqueObject' => $this->statistiqueObjectsClaims($request, true),
            'statistiqueChannel' => $this->statistiqueChannels($request, true),
            'statistiqueQualificationPeriod'  => $this->statistiqueQualifications($request, true),
            'statistiqueTreatmentPeriod'  => $this->statistiqueTreatments($request, true),
            'statistiqueGraphePeriod' => $this->statistiqueEvolutions($request, true),
        ];

        return response()->json($statistiques, 200);

    }


}
