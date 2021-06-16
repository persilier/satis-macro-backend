<?php


namespace Satis2020\ReportingClaimAnyInstitution\Providers;


use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;


/**
 * Class ReportingClaimAnyInstitutionServiceProvider
 * @package Satis2020\ReportingClaimAnyInstitution\Providers
 */
class ReportingClaimAnyInstitutionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerResources();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register all the resources of the package.
     */
    protected function registerResources()
    {
        $this->registerRoutes();
        $this->registerConfig();
    }

    /**
     * Register all the routes of the package.
     */
    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function (){
            $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        });
    }


    protected function registerConfig(){

    }


    /**
     * @return array
     */
    protected function routeConfiguration()
    {
        return [
            'namespace' => 'Satis2020\ReportingClaimAnyInstitution\Http\Controllers',
            'middleware' => ['api']
        ];
    }

}
