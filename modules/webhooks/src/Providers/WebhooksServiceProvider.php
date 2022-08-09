<?php

namespace Satis2020\Webhooks\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Resources\Json\JsonResource;
use Satis2020\Webhooks\Services\SendEventService;

/**
 * Class WebhooksServiceProvider
 * @package Satis2020\Webhooks\Providers
 */
class WebhooksServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        config(['app.timezone' => 'Africa/Porto-Novo']);

        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();
        $this->registerResources();
        $this->registerCommands();
    }


    protected function registerCommands()
    {

        if ($this->app->runningInConsole()) {
            $this->commands([


            ]);

            $this->app->booted(function () {
                //$this->app->make(Schedule::class)->command('service:generate-relance')->twiceDaily(7, 14);
            });
        }
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
        $this->registerObservers();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMiddlewares();
        $this->registerRoutes();
        $this->registerLaravelPassportIssues();
        $this->registerPolicies();
        $this->registerFactories();

    }


    /**
     * Publish the seeders
     */
    protected function publishesSeeders()
    {

    }

    /**
     * Publish the factories
     */
    protected function publishesFactories()
    {
        $this->publishes([
            __DIR__ . '/../../database/factories/' => database_path('factories')
        ], 'satis2020-escalation-factories');
    }

    /**
     * Publish the configs
     */
    protected function publishesConfigs()
    {
        $this->publishes([
            __DIR__ . '/../../config/' => config_path(''),
        ], 'satis2020-escalation-config');
    }

    /**
     * Register the Dependencies Service Providers
     */
    protected function registerDependencyServiceProviders()
    {
        //$this->app->register(\Laravel\Passport\PassportServiceProvider::class);
    }

    /**
     * Register all the facades of the package.
     */
    protected function registerFacades()
    {
        $this->app->singleton('SendEvent', function ($app) {
            return new SendEventService();
        });

    }

    /**
     * Register the Observers for the Models
     */
    protected function registerObservers()
    {

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
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'Webhooks');
    }

    /**
     * Register the Views
     */
    protected function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'Webhooks');
    }

    /**
     * Register the middlewares
     */
    protected function registerMiddlewares()
    {

    }

    /**
     * Register the Laravel Passport Issues
     */
    protected function registerLaravelPassportIssues()
    {

        Passport::tokensExpireIn(Carbon::now()->addDay());
        Passport::refreshTokensExpireIn(Carbon::now()->addMonth());
        Passport::enableImplicitGrant();
        Passport::tokensCan($this->getScopes());
    }

    /**
     * get the list of scopes of the application
     * @return array
     */
    protected function getScopes()
    {
        return [];
    }

    /**
     * Register the application's policies.
     *
     * @return void
     */
    public function registerPolicies()
    {
        foreach ($this->policies() as $key => $value) {
            Gate::policy($key, $value);
        }
    }

    /**
     * Register all the routes of the package.
     */
    protected function registerFactories()
    {
        $this->loadFactoriesFrom(__DIR__ . '/../../database/factories');
    }

    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return $this->policies;
    }

    /**
     * Register all the routes of the package.
     */
    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        });
    }


    /**
     * Routes configurations
     * @return array
     */
    protected function routeConfiguration()
    {
        return [
            'middleware' => ['api']
        ];
    }




}
