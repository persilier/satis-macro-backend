<?php

namespace Satis2020\ReviveStaff\Http\Controllers\ReviveStaff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Notifications\ReviveStaff;
use Symfony\Component\HttpFoundation\Response;

class ReviveStaffController extends ApiController
{

    use \Satis2020\ServicePackage\Traits\Notification;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');

        $this->middleware('permission:revive-staff')->only(['store']);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Claim $claim)
    {
        $rules = [
            'text' => 'required',
        ];

        $this->validate($request, $rules);

        Notification::send($this->getStaffToReviveIdentities($claim), new ReviveStaff($claim, $request->text));

        return response()->json($claim, Response::HTTP_OK);
    }

}
