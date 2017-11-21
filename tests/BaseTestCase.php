<?php

namespace JKniest\Tests;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Route;
use JKniest\HtmlCache\HtmlCacheServiceProvider;
use JKniest\HtmlCache\Http\Middleware\CacheHtml;
use Orchestra\Testbench\TestCase;

class BaseTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Route::group(['middleware' => CacheHtml::class], function () {
            Route::any('/example', function () {
                return 'Example value: ' . request('test');
            });
            Route::get('/another', function () {
                return 'Another value: ' . request('test');
            });
            Route::get('/error', function () {
                return response('Error: ' . request('test'), 500);
            });
            Route::get('/validation', function () {
                request()->validate([
                    'name' => 'required'
                ]);
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