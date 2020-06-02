<?php

namespace Satis2020\Institution\Http\Controllers\Institutions;

use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\InstitutionTrait;

class InstitutionUnitController extends ApiController
{
    use InstitutionTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Institution $institution
     * @return \Illuminate\Http\JsonResponse
     * @throws RetrieveDataUserNatureException
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     */
    public function index($institution)
    {
        $institution = $this->getOneMyInstitution($institution, $this->institution()->id);
        return response()->json($institution->only(['units']), 200);
    }

}
