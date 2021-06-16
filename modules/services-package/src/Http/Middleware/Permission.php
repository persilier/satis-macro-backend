<?php
namespace Satis2020\ServicePackage\Http\Middleware;
use Closure;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
class Permission
{
    use ApiResponser;
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $permission
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        $user = Auth::user();
        if(!in_array($permission, $user->getPermissionsViaRoles()->pluck('name')->toArray())){
            return $this->errorResponse('L\'utilisateur n\'a pas la bonne autorisation', 401);
        }
        return $next($request);
    }
}
