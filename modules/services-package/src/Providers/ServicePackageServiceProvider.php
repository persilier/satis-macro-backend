<?php
namespace Satis2020\ServicePackage\Providers;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ServicePackageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerResources();
    }

    /**
     * Register all the resources of the package.
     */
    protected function registerResources()
    {
        $this->publishesConfigs();
        $this->publishesSeeders();
        $this->publishesFactories();

        $this->registerDependencyServiceProviders();
        $this->registerFacades();
        $this->registerMigrations();
        $this->registerViews();
        $this->registerMiddlewares();
        $this->registerRoutes();

    }


    /**
     * Publish the seeders
     */
    protected function publishesSeeders()
    {
        $this->publishes([
            __DIR__ . '/../../database/seeds/Publishes/' => database_path('seeds')
        ], 'satis2020-seeds');
    }

    /**
     * Publish the factories
     */
    protected function publishesFactories()
    {
        $this->publishes([
            __DIR__.'/../../database/factories/' => database_path('factories')
        ], 'satis2020-factories');
    }

    /**
     * Publish the configs
     */
    protected function publishesConfigs()
    {
        $this->publishes([
            __DIR__.'/../../config/' => config_path(''),
        ], 'satis2020-config');
    }

    /**
     * Register the Dependencies Service Providers
     */
    protected function registerDependencyServiceProviders()
    {

    }

    /**
     * Register all the facades of the package.
     */
    protected function registerFacades()
    {
        $this->app->singleton('Handler', function ($app){
            return new \Satis2020\ServicePackage\Exceptions\Handler();
        });
    }



    /**
     * Register all the migrations of the package.
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * Register the Views
     */
    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'ServicePackage');
    }

    /**
     * Register the middlewares
     */
    protected function registerMiddlewares()
    {
        $this->app['router']->aliasMiddleware('transform.input', \Satis2020\ServicePackage\Http\Middleware\TransformInput::class);
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
            'middleware' => ['api']
        ];
    }

}
