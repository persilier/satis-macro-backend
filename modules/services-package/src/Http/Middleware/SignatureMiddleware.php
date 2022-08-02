<?php
namespace Satis2020\ServicePackage\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class SignatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @param  String  $headerName
     * @return mixed
     */
    public function handle($request, Closure $next, $headerName = 'X-Name')
    {
        $response = $next($request);
        $response->headers->set($headerName, config('packagetest.name'));
        return $response;
    }
}
