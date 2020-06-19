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
        $claim->load($this->getRelations());

        $claim->duplicates = $this->getDuplicates($claim);

        return response()->json($claim, 200);
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
            return redirect()->route('claim.awaiting.assignment.show', $claim->id);
        }

        $claim->delete();
        return redirect()->route('claim.awaiting.assignment.show', $duplicate->id);
    }

}
