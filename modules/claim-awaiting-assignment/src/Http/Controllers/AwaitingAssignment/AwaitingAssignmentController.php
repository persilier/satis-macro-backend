<?php

namespace Satis2020\ClaimAwaitingAssignment\Http\Controllers\AwaitingAssignment;

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
<<<<<<< HEAD
        $paginationSize = \request()->query('size');
        $key = \request()->query('key');
        $type = \request()->query('type');
        $claims = $this->getClaimsQuery()->with($this->getRelations());
=======
        $type = $request->query('type','normal');

        $claims = $this->getClaimsQuery()
            ->when($type==Claim::CLAIM_UNSATISFIED,function ($query){
                $query->where('status',Claim::CLAIM_UNSATISFIED);
            })
            ->get()->map(function ($item, $key) {

            $item = Claim::with($this->getRelations())->find($item->id);

            $item->is_rejected = false;

            if (!is_null($item->activeTreatment)) {

                $item->activeTreatment->load($this->getActiveTreatmentRelationsAwaitingAssignment());

                if (!is_null($item->activeTreatment->rejected_at) && !is_null($item->activeTreatment->rejected_reason)
                    && !is_null($item->activeTreatment->responsibleUnit)) {
                    $item->is_rejected = true;
                }
>>>>>>> develop

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
                    $claims = $claims->whereHas("claimer", function ($query) use ($key) {
                        $query->where('firstname', 'like', "%$key%")
                            ->orWhere('lastname', 'like', "%$key%")
                            ->orwhereJsonContains('telephone', $key)
                            ->orwhereJsonContains('email', $key);
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

        $this->activityLogService->store("Fusion de réclamation pour les cas de doublon",
            $this->institution()->id,
            $this->activityLogService::FUSION_CLAIM,
            'claim',
            $this->user()
        );

        return response()->json($this->showClaim($redirect), 200);
    }

}
