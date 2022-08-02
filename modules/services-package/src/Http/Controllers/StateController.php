<?php


namespace Satis2020\ServicePackage\Http\Controllers;


use Satis2020\ServicePackage\Services\StateService;

class StateController extends ApiController
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * StateController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->stateService = new StateService();
    }

    public function index($country_id)
    {
        return response($this->stateService->getStatesByCountry($country_id));
    }

}