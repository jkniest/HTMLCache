<?php

namespace JKniest\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Route;
use JKniest\HtmlCache\HtmlCacheServiceProvider;
use JKniest\HtmlCache\Http\Middleware\CacheHtml;

class BaseTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Route::group(['middleware' => CacheHtml::class], function () {
            Route::get('/example', function () {
                return 'Example value: '.session('test');
            });
            Route::any('/form', function () {
                return 'Example value: '.request('test');
            });
            Route::get('/another', function () {
                return 'Another value: '.session('test');
            });
            Route::get('/error', function () {
                return response('Error: '.session('test'), 500);
            });
            Route::get('/validation', function () {
                request()->validate([
                    'name' => 'required',
                ]);
            });
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            HtmlCacheServiceProvider::class,
        ];
    }
}
