<?php

namespace JKniest\HtmlCache;

use Illuminate\Support\ServiceProvider;

/**
 * The main service provider for this application.
 *
 * @category Core
 * @author   Jordan Kniest <mail@jkniest.de>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://jkniest.de
 */
class HtmlCacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrapping the package.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/htmlcache.php' => config_path('htmlcache.php'),
        ], 'htmlcache');

        $this->mergeConfigFrom(__DIR__.'/config/htmlcache.php', 'htmlcache');
    }
}
