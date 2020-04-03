<?php

namespace Satis2020\ServicePackage\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(request()->has('lang') && in_array(request()->lang, ['fr', 'en'])){
            App::setLocale(request()->lang);
        }

        return $next($request);
    }
}