<?php


namespace Satis2020\TransferClaimToCircuitUnit\Providers;


use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TransferClaimToCircuitUnitServiceProvider extends ServiceProvider
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
     */
    protected function routeConfiguration()
    {
        return [
            'namespace' => 'Satis2020\TransferClaimToCircuitUnit\Http\Controllers',
            'middleware' => ['api']
        ];
    }

}
