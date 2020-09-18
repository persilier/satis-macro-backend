<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\Clients;

use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\ClientTrait;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($institutionId)
    {
        $clients = $this->getAllClientByInstitution($institutionId);
        return response()->json($clients, 200);
    }

}
