<?php
namespace Satis2020\ServicePackage\Http\Middleware;
use Closure;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\StaffManagement;

class MyStaff
{
    use ApiResponser, StaffManagement, DataUserNature;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Satis2020\ServicePackage\Exceptions\CustomException
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function handle($request, Closure $next)
    {
        $this->checkIfStaffBelongsToMyInstitution($request->route('staff'), $this->institution()->id);
        return $next($request);
    }
}
