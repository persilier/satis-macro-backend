<?php
namespace Satis2020\ServicePackage\Http\Middleware;
use Closure;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Traits\DataUserNature;

class ActivePilot
{
    use ApiResponser, DataUserNature;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
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
