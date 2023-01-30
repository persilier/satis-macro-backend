<?php

namespace Satis2020\ServicePackage\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Satis2020\ServicePackage\Traits\DataUserNature;

class ActivePilot
{
    use ApiResponser, DataUserNature;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws RetrieveDataUserNatureException
     */
    public function handle($request, Closure $next)
    {
        $staff = $this->staff();

        if (!$staff->is_active_pilot) {
            return $this->errorResponse('Unauthorized', 401);
        }

        return $next($request);
    }
}
