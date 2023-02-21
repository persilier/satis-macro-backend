<?php

namespace Satis2020\PredictionAi\Http\Controllers\PredictionAi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Rules\TranslatableFieldUnicityRules;
use Satis2020\ServicePackage\Traits\PredictionAITrait;

class PredictionAiController extends ApiController
{
    use PredictionAITrait;

    public function __construct()
    {
        $this->middleware('client.credentials');
    }

    public function index()
    {
        return response()->json($this->institutionCategoryObject(), 200);
    }

    public function indexInstitutionClaim()
    {
        return response()->json($this->institutionClaim(), 200);
    }

    public function indexInstitutionTUnitTreatedClaimWithObject()
    {
        return response()->json($this->institutionTUnitTreatedClaimWithObject(), 200);
    }


    public function indexInstitutionClaimTreated()
    {
        return response()->json($this->institutionClaimTreated(), 200);
    }



}
