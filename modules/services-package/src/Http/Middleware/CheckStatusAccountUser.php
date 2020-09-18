<?php

namespace Satis2020\ServicePackage\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Traits\ApiResponser;

class CheckStatusAccountUser
{
    use ApiResponser;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(!is_null(Auth::user()->disabled_at)){

            return $this->errorResponse('Aucune action n\'est autorisé, votre compte est désactivé.', 401);
        }
        return $next($request);
    }
}
