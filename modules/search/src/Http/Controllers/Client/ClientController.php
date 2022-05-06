<?php

namespace Satis2020\Search\Http\Controllers\Client;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Traits\Search;

class ClientController extends ApiController
{

    use Search, Notification;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Institution $institution
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, $institution)
    {
        return response()->json($this->searchClient($request,$institution), JsonResponse::HTTP_OK);
    }

}
