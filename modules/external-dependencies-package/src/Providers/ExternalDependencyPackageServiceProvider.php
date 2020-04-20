<?php

namespace Satis2020\ExternalDependency\Providers;
use Illuminate\Support\ServiceProvider;

class ExternalDependencyPackageServiceProvider extends ServiceProvider
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
        $this->registerDependencyPackageServiceProviders();
        $this->registerAliases();
    }

    /**
     * Register the Dependencies Service Providers
     */
    protected function registerDependencyPackageServiceProviders()
    {
        $this->app->register(\Spatie\Permission\PermissionServiceProvider::class);
        $this->app->register(\Laravel\Passport\PassportServiceProvider::class);

    }

    /**
     * Register the aliases
     */
    protected function registerAliases()
    {
        $this->app->alias('cache', \Illuminate\Cache\CacheManager::class);
    }
}
