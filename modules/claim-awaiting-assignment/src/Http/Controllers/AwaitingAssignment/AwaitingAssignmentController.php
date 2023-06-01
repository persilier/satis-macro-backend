<?php

namespace Satis2020\ClaimAwaitingAssignment\Http\Controllers\AwaitingAssignment;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\AwaitingAssignment;

class AwaitingAssignmentController extends ApiController
{

    use AwaitingAssignment;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-awaiting-assignment')->only(['index']);
        $this->middleware('permission:show-claim-awaiting-assignment')->only(['show']);
        $this->middleware('permission:merge-claim-awaiting-assignment')->only(['merge']);

        $this->middleware('active.pilot')->only(['index', 'show', 'merge']);

        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $paginationSize = \request()->query('size', 10);
        $key = \request()->query('key');
        $type = \request()->query('type', null);
        $claims = $this->getClaimsQuery()->with(array_merge($this->getRelations()));

        if ($key) {
            switch ($type) {
                case 'reference':
                    $claims = $claims->where('reference', 'LIKE', "%$key%");
                    break;
                case 'claimObject':
                    $claims = $claims->whereHas("claimObject", function ($query) use ($key) {
                        $query->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                    });
                    break;
                default:
                    $claims = $claims->where(function ($query) use ($key) {
                        $query->whereHas("claimer", function ($query) use ($key) {
                            $query->where('firstname', 'like', "%$key%")
                                ->orWhere('lastname', 'like', "%$key%")
                                ->orwhereJsonContains('telephone', $key)
                                ->orwhereJsonContains('email', $key);
                        })->orWhere('reference', 'LIKE', "%$key%")->orWhereHas("claimObject", function ($query3) use ($key) {
                            $query3->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                        })->orWhereHas("unitTargeted", function ($query4) use ($key) {
                            $query4->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                        });
                    });
                    break;
            }
        }
        
        return response()->json($claims->paginate($paginationSize), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function show(Claim $claim)
    {
        return response()->json($this->showClaim($claim), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Claim $claim
     * @param Claim $duplicate
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function merge(Request $request, Claim $claim, Claim $duplicate)
    {
        $duplicates = $this->getDuplicates($claim);

        $duplicateKey = $duplicates->search(function ($item, $key) use ($duplicate) {
            return $item->id == $duplicate->id;
        });

        if ($duplicateKey === false) {
            throw new CustomException("Can't merge these claims. No compatibility");
        }

        $duplicate = $duplicates->get($duplicateKey);

        if (!$duplicate->is_mergeable) {
            throw new CustomException("Can't merge these claims. The required minimum probability is not reached");
        }

        $rules = ['keep_claim' => 'required|boolean'];

        $this->validate($request, $rules);

        if ($request->keep_claim) {
            $duplicate->delete();
            $redirect = $claim;
        } else {
            $claim->delete();
            $redirect = $duplicate;
        }

        $this->activityLogService->store(
            "Fusion de réclamation pour les cas de doublon",
            $this->institution()->id,
            $this->activityLogService::FUSION_CLAIM,
            'claim',
            $this->user()
        );

        return response()->json($this->showClaim($redirect), 200);
    }
}
