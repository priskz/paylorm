<?php

namespace Priskz\Paylorm\Data\Mongo\Laravel;

use Jenssegers\Mongodb\Connection;
use Jenssegers\Mongodb\MongodbServiceProvider;

class ServiceProvider extends MongodbServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        // Add database driver.
        $this->app->resolving('db', function ($db)
        {
            $db->extend('mongodb', function ($config)
            {
                return new Connection($config);
            });
        });
    }
}