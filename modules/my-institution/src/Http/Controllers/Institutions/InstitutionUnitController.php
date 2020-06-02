<?php

namespace Satis2020\MyInstitution\Http\Controllers\Institutions;

use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
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
     * @return \Illuminate\Http\JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function index()
    {
        $institution = $this->institution();
        return response()->json($institution->load('units'), 200);
    }

}
