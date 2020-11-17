<?php

namespace Satis2020\StaffHistory\Http\Controllers\History;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;


/**
 * Class CreateClaimController
 * @package Satis2020\StaffHistory\Http\Controllers\UnitType
 */
class TreatClaimController extends ApiController
{
    use DataUserNature;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:history-list-treat-claim')->only(['index']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Claim::with([
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment'
        ])->whereHas('activeTreatment' , function ($query){
            $query->where('responsible_staff_id', $this->staff()->id);
        } )->get(), 200);
    }

}
