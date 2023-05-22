<?php

namespace Satis2020\StaffHistory\Http\Controllers\History;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\StaffManagement;


/**
 * Class CreateClaimController
 * @package Satis2020\StaffHistory\Http\Controllers\UnitType
 */
class CreateClaimController extends ApiController
{
    use StaffManagement, DataUserNature;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:history-list-create-claim')->only(['index']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $staff_id = \request()->query('staff_id');
        if ($staff_id == null) {
            $staff_id = $this->staff()->id;
        }

        return response()->json(Claim::with([
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment'
        ])->where('created_by', $staff_id)->get(), 200);
    }

    public function create()
    {
        $institutionId = request('institution_id', $this->institution()->id);
        return response()->json($this->getRegisteredClaims($institutionId), 200);
    }
}
