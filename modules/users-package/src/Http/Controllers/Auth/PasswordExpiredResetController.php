<?php
namespace Satis2020\UserPackage\Http\Controllers\Auth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Requests\UpdatePasswordRequest;
use Satis2020\ServicePackage\Services\Auth\UpdatePasswordService;

/**
 * Class PasswordResetController
 * @package Satis2020\UserPackage\Http\Controllers\Auth
 */
class PasswordExpiredResetController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('set.language');
    }


    /**
     * Update Password
     * @param UpdatePasswordRequest $request
     * @param UpdatePasswordService $updatePasswordService
     * @return Application|ResponseFactory|Response
     * @throws CustomException
     */
    public function update(UpdatePasswordRequest $request,UpdatePasswordService $updatePasswordService)
    {
        $user = $updatePasswordService->getByEmail($request->email);
        $updatePasswordService->update($request->new_password, $user);
        return response("Mot de passe mis à jour avec succès",Response::HTTP_OK);
    }


}
