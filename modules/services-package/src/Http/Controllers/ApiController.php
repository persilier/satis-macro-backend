<?php
namespace Satis2020\ServicePackage\Http\Controllers;
use Satis2020\ServicePackage\Traits\ApiResponser;

class ApiController extends Controller
{
    use ApiResponser;

    /**
     * ApiController constructor.
     */
    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('Cors');
    }
}
