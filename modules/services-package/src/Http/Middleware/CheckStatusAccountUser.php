<?php

namespace Satis2020\ServicePackage\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckStatusAccountUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(Auth::check() && !is_null(Auth::user()->disabled_at)){

            return response()->json('Aucune action n\'est autorisé, votre compte est désactivé.', 401);
        }
        return $next($request);
    }
}
