<?php

namespace Klaravel\DbLogger;

use Klaravel\DbLogger\DbLogger;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
	/**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // files to publish
        $this->publishes($this->getPublished());

        // if any of logging type is enabled we will listen database to get all
        // executed queries
        if (config('dblogger.log_queries') || 
            config('dblogger.log_slow_queries')) 
        {
            // create logger class
            $logger = $this->app->make(DbLogger::class);

            // listen to database queries
            $this->app['db']->listen(function ($query, $bindings = null, $time = null) use ($logger) {
                $logger->log($query, $bindings, $time);
            });
        }
    }

    /**
     * Get files to be published
     *
     * @return array
     */
    protected function getPublished()
    {
        return [
            realpath(__DIR__ .
                '/config/dblogger.php') =>
                (function_exists('config_path') ?
                    config_path('dblogger.php') :
                    base_path('config/dblogger.php')),
        ];
    }
}