<?php
namespace Satis2020\ServicePackage\Http\Controllers;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\Notification;

/**
 * Class ApiController
 * @package Satis2020\ServicePackage\Http\Controllers
 */
class ApiController extends Controller
{
    use ApiResponser, DataUserNature, Notification;
    /**
     * ApiController constructor.
     */
    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('status.account');
    }
}
