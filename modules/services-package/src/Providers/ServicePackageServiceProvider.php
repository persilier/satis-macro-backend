<?php

namespace Satis2020\ServicePackage\Providers;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Policies\UserPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Satis2020\ServicePackage\Rules\SmtpParametersRules;
use Illuminate\Support\Facades\DB;

/**
 * Class ServicePackageServiceProvider
 * @package Satis2020\ServicePackage\Providers
 */
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

                \Satis2020\ServicePackage\Console\Commands\ReportingDayCommand::class,
                \Satis2020\ServicePackage\Console\Commands\ReportingWeekCommand::class,
                \Satis2020\ServicePackage\Console\Commands\ReportingMonthCommand::class,
                \Satis2020\ServicePackage\Console\Commands\ReportingQuarterlyCommand::class,
                \Satis2020\ServicePackage\Console\Commands\ReportingBiannualCommand::class,
                \Satis2020\ServicePackage\Console\Commands\RelanceCommand::class,
                \Satis2020\ServicePackage\Console\Commands\SendSMSCommand::class,
                \Satis2020\ServicePackage\Console\Commands\SendMailCommand::class,
                \Satis2020\ServicePackage\Console\Commands\RegulatorySemesterReportingCommand::class,
                \Satis2020\ServicePackage\Console\Commands\DeletingTestData::class,
            ]);

            $this->app->booted(function () {
                //$this->app->make(Schedule::class)->command('service:generate-relance')->twiceDaily(7, 14);
                // $this->app->make(Schedule::class)->command('service:generate-reporting-day')->twiceDaily(0, 13);
                // $this->app->make(Schedule::class)->command('service:generate-reporting-week')->mondays();
                // $this->app->make(Schedule::class)->command('service:generate-reporting-month')->monthlyOn(1, '01:00')->monthlyOn(1, '13:00');
                // $this->app->make(Schedule::class)->command('service:generate-reporting-quarterly')->quarterly()->between(07, 18);
                // $this->app->make(Schedule::class)->command('service:generate-reporting-biannual')->quarterly()->quarterly();

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
        ], 'satis2020-factories');
    }

    /**
     * Publish the configs
     */
    protected function publishesConfigs()
    {
        $this->publishes([
            __DIR__ . '/../../config/' => config_path(''),
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
        $this->app->singleton('Handler', function ($app) {
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
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'ServicePackage');
    }

    /**
     * Register the Views
     */
    protected function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'ServicePackage');
    }

    /**
     * Register the middlewares
     */
    protected function registerMiddlewares()
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('transform.input', \Satis2020\ServicePackage\Http\Middleware\TransformInput::class);
        $router->aliasMiddleware('set.language', \Satis2020\ServicePackage\Http\Middleware\SetLanguage::class);
//        $router->aliasMiddleware('client-from-my-institution.credentials', \Laravel\Passport\Http\Middleware\CheckClientCredentials::class);
        $router->aliasMiddleware('auth', \Satis2020\ServicePackage\Http\Middleware\Authenticate::class);
        $router->aliasMiddleware('status.account', \Satis2020\ServicePackage\Http\Middleware\CheckStatusAccountUser::class);
        $router->aliasMiddleware('scope', \Laravel\Passport\Http\Middleware\CheckForAnyScope::class);
        $router->aliasMiddleware('scopes', \Laravel\Passport\Http\Middleware\CheckScopes::class);
        $router->aliasMiddleware('permission', \Satis2020\ServicePackage\Http\Middleware\Permission::class);
        $router->aliasMiddleware('verification', \Satis2020\ServicePackage\Http\Middleware\Verification::class);
        $router->aliasMiddleware('mystaff', \Satis2020\ServicePackage\Http\Middleware\MyStaff::class);
        $router->aliasMiddleware('active.pilot', \Satis2020\ServicePackage\Http\Middleware\ActivePilot::class);
        $router->aliasMiddleware('client.credentials', \Laravel\Passport\Http\Middleware\CheckClientCredentials::class);
        $router->aliasMiddleware('allow.pilot.collector.to.discussion', \Satis2020\ServicePackage\Http\Middleware\AllowPilotCollectorToDiscussion::class);

    }

    /**
     * Register the Laravel Passport Issues
     */
    protected function registerLaravelPassportIssues()
    {

        $this->routeAuthRefreshToken();
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

    protected function routeAuthRefreshToken(){
        Passport::routes();
//        Route::post('/oauth/token', [
//            'uses' => '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken',
//           // 'middleware' => 'throttle:3,120',
//        ]);
//        Route::post('/token/refresh', [
//            'middleware' => ['web', 'auth'],
//            'uses' => '\Laravel\Passport\Http\Controllers\TransientTokenController@refresh',
//            'as' => 'passport.token.refresh',
//        ]);
    }

    protected function registerMailSmtpConfigs()
    {

        $mailSmtpConfigs = DB::table('metadata')->where('name', 'mail-parameters')->first();

        if (!is_null($mailSmtpConfigs)) {

            if (!is_null($mailSmtpConfigs->data)) {

                $parameters = json_decode($mailSmtpConfigs->data);

                if (property_exists($parameters, 'state')) {
                    if ($parameters->state == 1) {

                        $config = [
                            'transport' => 'smtp',
                            'host' => $parameters->server,
                            'port' => $parameters->port,
                            'username' => $parameters->username,
                            'password' => $parameters->password,
                            'encryption' => $parameters->security,
                            'from' => $parameters->from,
                        ];

                        Config::set('mail', $config);
                    }
                }

            }
        }

    }

}
