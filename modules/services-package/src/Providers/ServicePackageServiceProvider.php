<?php
namespace Satis2020\ServicePackage\Providers;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Policies\UserPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
class ServicePackageServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

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
        Route::get('storage/assets/images/institutions/{filename}', function ($filename) {
            $path = storage_path() . '/storage/assets/images/institutions/' . $filename;

            if(!File::exists($path)) abort(404);

            $file = File::get($path);
            $type = File::mimeType($path);

            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);
            return $response;
        });
        JsonResource::withoutWrapping();
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
        //$this->app->register(\Laravel\Passport\PassportServiceProvider::class);
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
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'ServicePackage');
    }

    /**
     * Register the Views
     */
    protected function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'ServicePackage');
    }

    /**
     * Register the middlewares
     */
    protected function registerMiddlewares()
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('transform.input', \Satis2020\ServicePackage\Http\Middleware\TransformInput::class);
        $router->aliasMiddleware('set.language', \Satis2020\ServicePackage\Http\Middleware\SetLanguage::class);
        $router->aliasMiddleware('client.credentials', \Laravel\Passport\Http\Middleware\CheckClientCredentials::class);
        $router->aliasMiddleware('auth', \Satis2020\ServicePackage\Http\Middleware\Authenticate::class);
        $router->aliasMiddleware('scope', \Laravel\Passport\Http\Middleware\CheckForAnyScope::class);
        $router->aliasMiddleware('scopes', \Laravel\Passport\Http\Middleware\CheckScopes::class);
        $router->aliasMiddleware('permission', \Satis2020\ServicePackage\Http\Middleware\Permission::class);
        $router->aliasMiddleware('verification', \Satis2020\ServicePackage\Http\Middleware\Verification::class);
    }

    /**
     * Register the Laravel Passport Issues
     */
    protected function registerLaravelPassportIssues()
    {
        Passport::routes();
        Passport::tokensExpireIn(Carbon::now()->addDays(1));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
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
        $this->loadFactoriesFrom(__DIR__.'/../../database/factories');
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
