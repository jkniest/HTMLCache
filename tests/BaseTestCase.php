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

        $called = 1;

        Route::group(['middleware' => CacheHtml::class], function () use ($called) {
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
            Route::get('/header', function () use ($called) {
                return response('Header content', 200, ['header' => ++$called]);
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
