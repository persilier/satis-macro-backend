<?php

namespace Satis2020\UserPackage\Http\Controllers\Profile;

use Illuminate\Http\Response;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\UpdatePasswordRequest;
use Satis2020\ServicePackage\Services\Auth\UpdatePasswordService;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/***
 * Class UpdatePasswordController
 * @package Satis2020\UserPackage\Http\Controllers\Profile
 */
class UpdatePasswordController extends ApiController
{
    use VerifyUnicity, IdentityManagement;

    public function __construct()
    {
        parent::__construct();
        //$this->middleware('auth:api');
    }

    /**
     * Update Password
     * @param UpdatePasswordRequest $request
     * @param UpdatePasswordService $updatePasswordService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws RetrieveDataUserNatureException|CustomException
     */
    public function update(UpdatePasswordRequest $request, UpdatePasswordService $updatePasswordService)
    {
        $user = $updatePasswordService->update($request->new_password, $this->user());
        return response($user->load('identite'),Response::HTTP_OK);
    }
}