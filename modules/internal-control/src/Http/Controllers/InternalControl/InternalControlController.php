<?php

namespace Satis2020\InternalControl\Http\Controllers\InternalControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;
use Satis2020\ServicePackage\Traits\InternalControlTrait;
use Satis2020\ServicePackage\Traits\PredictionAITrait;

class InternalControlController extends ApiController
{
    use InternalControlTrait;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:internal-control-index')->only(["index"]);
        $this->middleware('permission:internal-control-claim-object-index')->only(["indexClaimObject"]);
        $this->middleware('permission:internal-control-store')->only(["store"]);

      //  $this->middleware('permission:internal-control-claim')->only(["indexClaimsInternalControl"]);
        $this->middleware('permission:internal-control-claim-detail')->only(["show"]);
    }

    public function index()
    {
        return response()->json($this->infoConfigInternalControl(), 200);
    }

    public function indexClaimObject()
    {
        return response()->json(ClaimObject::where("internal_control",true)->get(), 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "state" => "required|boolean"
        ]);

        $config = $this->storeConfiguration($request->state);

        return response()->json($config, 201);
    }

    public function indexClaimsInternalControl(Request $request){
        return response()->json($this->claim($request), 201);
    }

    public function show($id){

        return response()->json($this->getClaimsInfoWithClaimObjectForInternalControl($id), 201);;
    }




}
