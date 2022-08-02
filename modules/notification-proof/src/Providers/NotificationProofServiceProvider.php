<?php
namespace Satis2020\NotificationProof\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Satis2020\ServicePackage\Traits\ApiResponser;

class NotificationProofServiceProvider extends ServiceProvider
{
    use ApiResponser;
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




    /**
     * Routes configurations
     */
    protected function routeConfiguration()
    {
        return [
            'namespace' => 'Satis2020\NotificationProof\Http\Controllers',
            'middleware' => ['api']
        ];
    }

}
