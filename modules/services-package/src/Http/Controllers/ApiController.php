<?php
namespace Satis2020\ServicePackage\Http\Controllers;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Satis2020\ServicePackage\Traits\DataUserNature;

class ApiController extends Controller
{
    use ApiResponser, DataUserNature;
    /**
     * ApiController constructor.
     */
    public function __construct()
    {
        $this->middleware('set.language');
    }
}
