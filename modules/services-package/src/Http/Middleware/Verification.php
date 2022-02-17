<?php
namespace Satis2020\ServicePackage\Http\Middleware;
use Closure;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
class Verification
{
    use ApiResponser;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($user = Auth::user()){
            if(!$user->isVerified()){
                return $this->errorResponse('Non 40vérifié. L\'utilisateur doit confirmer son compte.', 401);
            }
        }
        return $next($request);
    }
}
