<?php


namespace Satis2020\MyInstitutionTypeRole\Providers;


use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Class AnyInstitutionTypeRoleServiceProvider
 * @package Satis2020\StaffHistory\Providers
 */
class MyInstitutionTypeRoleServiceProvider extends ServiceProvider
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
     * Routes PerformanceIndicator
     * @return array
     */
    protected function routeConfiguration()
    {
        return [
            'namespace' => 'Satis2020\MyInstitutionTypeRole\Http\Controllers',
            'middleware' => ['api']
        ];
    }

}
