<?php

namespace Satis2020\ExternalDependency\Providers;
use Illuminate\Support\ServiceProvider;

/**
 * Class ExternalDependencyPackageServiceProvider
 * @package Satis2020\ExternalDependency\Providers
 */
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
        $this->app->register(\Cviebrock\EloquentSluggable\ServiceProvider::class);
        $this->app->register(\Maatwebsite\Excel\ExcelServiceProvider::class);
        $this->app->register(\Barryvdh\DomPDF\ServiceProvider::class);

    }

    /**
     * Register the aliases
     */
    protected function registerAliases()
    {
        $this->app->alias('cache', \Illuminate\Cache\CacheManager::class);
        $this->app->alias('Excel', \Maatwebsite\Excel\Facades\Excel::class);
        $this->app->alias('PDF', \Barryvdh\DomPDF\Facade::class);
    }
}
