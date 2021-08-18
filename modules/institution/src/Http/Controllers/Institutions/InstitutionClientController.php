<?php

namespace Satis2020\Institution\Http\Controllers\Institutions;

use Illuminate\Database\Eloquent\Builder;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;

class InstitutionClientController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Institution $institution
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Institution $institution)
    {
        $institution->client_institutions->load(['client.identite', 'accounts']);
        return response()->json([
            'client_institutions' => $institution->only('client_institutions')['client_institutions'],
            'units' => $institution->units()
                ->whereHas('unitType', function ($q) {
                    $q->where('can_be_target', true);
                })->get()
        ], 200);
    }

}
