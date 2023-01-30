<?php

namespace Satis2020\UserPackage\Http\Controllers\User;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\User;
use Illuminate\Http\Response;
use Satis2020\UserPackage\Http\Resources\PermissionCollection;

class UserPermissionController extends ApiController
{
    /**
     * UserPermissionController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @return PermissionCollection
     */
    public function index(User $user)
    {
        return new PermissionCollection($user->getPermissionsViaRoles());
    }

}
