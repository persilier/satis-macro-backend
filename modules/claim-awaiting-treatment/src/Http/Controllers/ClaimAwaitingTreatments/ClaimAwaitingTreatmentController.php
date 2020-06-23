<?php

namespace Satis2020\ClaimAwaitingTreatment\Http\Controllers\ClaimAwaitingTreatments;

use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\ClaimAwaitingTreatment;
use Illuminate\Http\Request;

class ClaimAwaitingTreatmentController extends ApiController
{
    use ClaimAwaitingTreatment;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:list-claim-awaiting-treatment')->only(['index']);
        $this->middleware('permission:show-claim-awaiting-treatment')->only(['show']);
        $this->middleware('permission:merge-claim-awaiting-treatment')->only(['merge']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function index()
    {

        $claims = $this->getClaimsQuery($this->staff()->id)->get()->map(function ($item, $key) {
            $item = Claim::with($this->getRelations())->find($item->id);
            $item->is_duplicate = $this->getDuplicatesQuery($this->getClaimsQuery($this->staff()->id), $item)->exists();
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

        if ($this->getDuplicates($claim)->search(function ($item, $key) use ($duplicate) {
                return $item->id == $duplicate->id;
            }) === false) {
            throw new CustomException("Can't merge these claims. No compatibility");
        }

        if ($claim->created_at >= $duplicate->created_at) {
            $duplicate->delete();
            $redirect = $claim;
        } else {
            $claim->delete();
            $redirect = $duplicate;
        }

        return response()->json($this->showClaim($redirect), 200);
    }

}
