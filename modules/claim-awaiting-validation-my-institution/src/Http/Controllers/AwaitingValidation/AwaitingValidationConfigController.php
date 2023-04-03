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
        $this->middleware('permission:list-claim-transferred-my-institution')->only(['getClaimTransferred']);

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
        $configs = $this->nowConfiguration();
        $search_text = \request()->query('search_text');

        return response()->json($this->getClaimsAwaitingValidationInMyInstitutionWithConfig($configs, $this->staff(), $this->institution(), true, $paginationSize, $key, $type, $search_text), 200);
    }

    public function getClaimTransferred()
    {
        $paginationSize = \request()->query('size');
        $key = \request()->query('key');
        $type = \request()->query('type');
        $search_text = \request()->query('search_text');
        $configs = $this->nowConfiguration();
        return response()->json($this->getClaimsTransferredInMyInstitutionWithConfig($configs, $this->staff(), $this->institution(), true, $paginationSize, $key, $type, $search_text), 200);
    }
}
