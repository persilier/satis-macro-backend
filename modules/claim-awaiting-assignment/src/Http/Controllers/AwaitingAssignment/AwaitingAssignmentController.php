<?php

namespace Satis2020\ClaimAwaitingAssignment\Http\Controllers\AwaitingAssignment;

use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\AwaitingAssignment;

class AwaitingAssignmentController extends ApiController
{

    use AwaitingAssignment;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-awaiting-assignment')->only(['index']);
        $this->middleware('permission:show-claim-awaiting-assignment')->only(['show']);
        $this->middleware('permission:merge-claim-awaiting-assignment')->only(['merge']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function index()
    {

        $claims = $this->getClaimsQuery()->get()->map(function ($item, $key) {

            $item = Claim::with($this->getRelations())->find($item->id);

            $item->is_rejected = false;

            if (!is_null($item->activeTreatment)) {

                $item->activeTreatment->load($this->getActiveTreatmentRelationsAwaitingAssignment());

                if (!is_null($item->activeTreatment->rejected_at) && !is_null($item->activeTreatment->rejected_reason)
                    && !is_null($item->activeTreatment->responsibleUnit)) {
                    $item->is_rejected = true;
                }

            }

            $item->is_duplicate = $this->getDuplicatesQuery($this->getClaimsQuery(), $item)->exists();

            return $item;

        });

        return response()->json($claims, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Claim $claim
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\RedirectResponse
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

        return response()->json($this->showClaim($redirect), 200);
    }

}
