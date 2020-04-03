<?php
namespace Satis2020\MetadataPackage\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Traits\ApiResponser;

class MetadataPackageServiceProvider extends ServiceProvider
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
        Route::bind('metadata', function($value){
            return Metadata::where('name',$value)->get()->first() ?? abort(404);
        });
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

        //if (! $this->app->configurationIsCached()) {
        $this->mergeConfigFrom(__DIR__.'/../../config/metadata.php','metadata');
        //}
    }

    /**
     * Routes configurations
     */
    protected function routeConfiguration()
    {
        return [
            'namespace' => 'Satis2020\MetadataPackage\Http\Controllers',
            'middleware' => ['api']
        ];
    }

}
