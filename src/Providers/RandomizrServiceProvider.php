<?php namespace Parsidev\Support\Providers;
    
    use Illuminate\Support\ServiceProvider;
    use Parsidev\Support\Services\Randomizr;
    
    class RandomizrServiceProvider extends ServiceProvider {
        
        protected $defer = true;
        
        public function register()
        {
            
            $this->app->singleton("randomizr", function()
                                  {
                                  $db = $this->app['db'];
                                  return new Randomizr($db);
                                  });
        }
        
        public function boot()
        {
            
            $this->publishes([
                             __DIR__ . '/../Config/randomizr.php' => config_path('randomizr.php'),
                             ]);
            
            // Register Artisan commands
            $this->commands(['Parsidev\Support\Commands\RandomizrPublisherCommand']);
        }
        
        /**
         * Get the services provided by the provider.
         *
         * @return array
         */
        public function provides()
        {
            return ['randomizr'];
        }
        
    }

