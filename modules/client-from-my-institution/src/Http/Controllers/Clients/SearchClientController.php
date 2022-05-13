<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\Clients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClientTrait;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\Search;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Symfony\Component\HttpFoundation\Response;

class SearchClientController extends ApiController
{
    use Search;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {

        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:store-client-from-my-institution')->only(['index']);

        $this->activityLogService = $activityLogService;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function index(Request $request)
    {
        $institution = $this->institution();
        return response()->json( $this->searchClient($request,$institution->id), 200);
    }


}

