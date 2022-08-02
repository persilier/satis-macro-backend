<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\Clients;

use Illuminate\Http\JsonResponse;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\ClientTrait;

/**
 * Class ClientInstitutionController
 * @package Satis2020\ClientFromAnyInstitution\Http\Controllers\Clients
 */
class ClientInstitutionController extends ApiController
{
    use ClientTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');

    }

    /**
     * Display a listing of the resource.
     *
     * @param $institutionId
     * @return JsonResponse
     */
    public function index($institutionId)
    {
        $paginationSize = \request()->query('size');
        $recherche = \request()->query('key');
        $clients = $this->getAllClientByInstitution($institutionId,true,$paginationSize,$recherche);
        return response()->json($clients, 200);
    }

}
