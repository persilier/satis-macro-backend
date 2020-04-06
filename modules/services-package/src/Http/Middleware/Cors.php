<?php


namespace Satis2020\ServicePackage\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        header("Access-Control-Allow-Origin: *");

        $headers = [
            "Access-Control-Allow-Methods" => "POST, GET, OPTIONS, PUT, DELETE",
            "Access-Control-Allow-Headers" => "Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With"
        ];

        if($request->getMethod() == "OPTIONS"){
            return response()->json('OK', 200, $headers);
        }

        $response = $next($request);

        foreach ($headers as $key => $value){
            $response->header($key, $value);
        }

        return $response;
    }

}
