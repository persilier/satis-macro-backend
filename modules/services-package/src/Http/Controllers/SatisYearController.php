<?php
namespace Satis2020\ServicePackage\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Satis2020\ServicePackage\Consts\Constants;

class SatisYearController extends ApiController
{

    public function index()
    {
        return response(['years'=>Constants::getSatisYearsFromCreation()]);
    }
}
