<?php
namespace Satis2020\ServicePackage\Http\Controllers;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Satis2020\ServicePackage\Traits\DataUserNature;

class ApiController extends Controller
{
    use ApiResponser, DataUserNature;
    protected $nature;
    protected $user;
    protected $institution;
    protected $staff;
    /**
     * ApiController constructor.
     */
    public function __construct()
    {
        $this->nature = $this->getNatureApp();
        $this->middleware('set.language');
    }
}
