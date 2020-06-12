<?php

namespace Satis2020\UpdateClaimAgainstAnyInstitution\Http\Controllers\Claims;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Traits\UpdateClaim;
class ClaimController extends ApiController
{
    use UpdateClaim;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-claim-incomplete-against-any-institution')->only(['index']);
        $this->middleware('permission:show-claim-incomplete-against-any-institution')->only(['show']);
        $this->middleware('permission:update-claim-incomplete-against-any-institution')->only(['update']);
    }


    public function index()
    {
        return response()->json(
            $this->getAllClaimCompleteOrIncomplete($this->institution()->id,'incomplete'),
        200);
    }


    public function show($claim)
    {
        return response()->json(
            $this->getOneClaimCompleteOrIncomplete($this->institution()->id, $claim ,'incomplete'),
        200);
    }


    public function edit($claim)
    {
        $claim = $this->getOneClaimCompleteOrIncomplete($this->institution()->id, $claim ,'incomplete');
        return response()->json([
            'claim' => $claim,
            'claimCategories' => ClaimCategory::all(),
            'institutions' => Institution::all(),
            'channels' => Channel::all(),
            'currencies' => Currency::all()
        ],200);
    }

    public function update($claim)
    {
        $claim = $this->getOneClaimCompleteOrIncomplete($this->institution()->id, $claim ,'incomplete');
        return response()->json([
            'claim' => $claim,
            'claimCategories' => ClaimCategory::all(),
            'institutions' => Institution::all(),
            'channels' => Channel::all(),
            'currencies' => Currency::all()
        ],200);
    }

}
