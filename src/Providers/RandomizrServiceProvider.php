<?php namespace Parsidev\Support\Providers;

use Illuminate\Support\ServiceProvider;
use Parsidev\Support\Services\Randomizr;

class RandomizrServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Name of the class instance bound the the IoC.
     *
     * @var string
     */
    protected $instance = 'Parsidev\Support\Services\Randomizr';

    /**
     * Name of the package.
     *
     * @var string
     */
    protected $package_name = 'parsidev/randomizr';

    /**
     * Contains the transformer configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['config']->package( $this->package_name, __DIR__.'/../Config/');

        $this->config = $this->app['config']->get('randomizr::randomizr');

        $this->app->singleton( $this->instance, function()
        {
            $db = $this->app['db'];

            return new Randomizr( $this->config, $db );
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Register the package
        $this->package( $this->package_name );

        // Register Artisan commands
        $this->commands(array('Parsidev\Support\Commands\RandomizrPublisherCommand'));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array($this->instance);
    }

}