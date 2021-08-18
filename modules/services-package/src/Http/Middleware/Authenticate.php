<?php


namespace Satis2020\ServicePackage\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\ApiResponser;

class Authenticate extends Middleware
{
    use ApiResponser;
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        return  $this->errorResponse('Unauthenticated. Please provide a valid access token', 401);
    }
}