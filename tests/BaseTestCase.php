<?php

namespace JKniest\Tests;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Route;
use JKniest\HtmlCache\HtmlCacheServiceProvider;
use JKniest\HtmlCache\Http\Middleware\CacheHtml;
use Orchestra\Testbench\TestCase;

class BaseTestCase extends TestCase
{
    private $oldExceptionHandler;

    protected function setUp()
    {
        parent::setUp();

        Route::group(['middleware' => CacheHtml::class], function () {
            Route::any('/example', function () {
                return 'Example value: ' . request('test');
            });
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            HtmlCacheServiceProvider::class
        ];
    }
}