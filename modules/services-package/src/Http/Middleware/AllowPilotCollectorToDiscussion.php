<?php

namespace Satis2020\ServicePackage\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Satis2020\ServicePackage\Traits\Metadata;

class AllowPilotCollectorToDiscussion
{
    use Metadata;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $concerne_role = null)
    {
        $config = $this->getMetadataByName('allow-pilot-collector-to-discussion');

        $allow_pilot = $config ? (int)  $config->allow_pilot  : null;
        $allow_collector = $config ? (int)  $config->allow_collector : null;

        $user_roles = $request->user() ? $request->user()->roles()->pluck('name') : null;

        $roles = $concerne_role ? explode("|", $concerne_role) : [];
        if (count($roles) > 0) {
            if (in_array("pilot",  $roles)) {
                if ($user_roles && $user_roles->contains("pilot") && $allow_pilot === 1) {
                    return $next($request);
                } else {
                    return  response()->json('L\'utilisateur n\'a pas la bonne autorisation.', 401);
                }
            }
            if (in_array("collector-filial-pro",  $roles)) {
                if ($user_roles && $user_roles->contains("collector-filial-pro") && $allow_collector === 1) {
                    return $next($request);
                } else {
                    return  response()->json('L\'utilisateur n\'a pas la bonne autorisation.', 401);
                }
            } else {
                return $next($request);
            }
        } else {
            return  response()->json('Veuillez préciser le role à verifier', 422);
        }
    }
}
