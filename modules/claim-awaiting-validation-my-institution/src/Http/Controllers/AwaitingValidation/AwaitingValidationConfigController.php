<?php

namespace Satis2020\ClaimAwaitingValidationMyInstitution\Http\Controllers\AwaitingValidation;

use Illuminate\Validation\ValidationException;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ConfigurationPilotTrait;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Rules\TreatmentCanBeValidateRules;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\AwaitingValidation;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\SeveralTreatment;

class AwaitingValidationConfigController extends ApiController
{

    use AwaitingValidation, SeveralTreatment, ConfigurationPilotTrait;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-awaiting-validation-my-institution')->only(['index']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function index()
    {
        $paginationSize = \request()->query('size');
        $key = \request()->query('key');
        $type = \request()->query('type');
        //dd($type);
        $configs = $this->nowConfiguration();
        return response()->json($this->getClaimsAwaitingValidationInMyInstitutionWithConfig($configs, $this->staff(), $this->institution(),true,$paginationSize,$key, $type ), 200);
    }


}
