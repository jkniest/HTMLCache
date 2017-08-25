<?php

namespace JKniest\Tests;

use Illuminate\Routing\Router;
use JKniest\HtmlCache\HtmlCacheServiceProvider;
use Orchestra\Testbench\TestCase;

class BaseTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $router = app(Router::class);
        $router->get('/example', function () {
            return 'Example value: ' . request('test');
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            HtmlCacheServiceProvider::class
        ];
    }
}