<?php

namespace Zareismail\NovaWizard;

use Illuminate\Support\ServiceProvider; 
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Nova; 

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wizard');

        $this->app->booted(function () {
            $this->routes(); 
            Nova::serving([$this, 'servingNova']);
        });
    }

    /**
     * Regsiter the Nova application manifests.
     * 
     * @return void
     */
    public function servingNova()
    {
        Nova::provideToScript([
            'wizard' => [
                'resources' => collect(Nova::$resources)->map(function($resource) {
                    if(is_subclass_of($resource, Contracts\Wizard::class)) {
                        return [
                            'key'  => $resource::uriKey(),
                            'step' => intval(session("{$resource::uriKey()}.step")),
                            'update' => ! is_subclass_of($resource, Contracts\IgnoreUpdateWizard::class), 
                            'navigable' => is_subclass_of($resource, Contracts\Navigable::class),
                        ];
                    } 
                })->filter()->values()
            ]
        ]); 

        Nova::script('zareisamil-wizard', __DIR__.'/../dist/js/tool.js');
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
                ->namespace(__NAMESPACE__.'\\Http\\Controllers')
                ->prefix('nova-api')
                ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
