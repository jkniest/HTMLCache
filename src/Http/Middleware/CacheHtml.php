<?php

namespace JKniest\HtmlCache\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * This middleware can cache a response object and return it the next time someone tries to
 * visit the page. The effects are that no database queries needs to be executed.
 *
 * @category Core
 * @package  JKniest\HTMLCache
 * @author   Jordan Kniest <mail@jkniest.de>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://jkniest.de
 */
class CacheHtml
{
    /**
     * Handle the incoming request. If the caching is disabled or the request is not a GET
     * request it will simply do nothing and only return the original response.
     *
     * If the caching is enabled and the user makes a GET request it tries to load the cached
     * version of this page. If there is no cached version the original response will be returned
     * and cached for the next time.
     *
     * @param Request  $request The incoming request
     * @param callable $next    The next middleware
     *
     * @return Response
     */
    public function handle(Request $request, $next)
    {
        $enabled = (
            $request->method() === 'GET' &&
            config('htmlcache.enabled') &&
            !$request->is($this->getIgnored())
        );

        if ($enabled) {
            $key = $this->getCacheKey($request->path());
            $time = config('htmlcache.minutes');

            $content = Cache::remember($key, $time, function () use ($next, $request) {
                return ($next($request))->getContent();
            });

            return response($content);
        }

        return $next($request);
    }

    /**
     * Generate the cache key for a given page. It will have the following syntax:
     * PREFIX_PAGE_LOCALE
     *
     * The prefix is configurable in the config file. The page is the current page the user
     * is visiting and the locale is the current locale (default: en)
     *
     * @param string $page The current page
     *
     * @return string
     */
    protected function getCacheKey(string $page)
    {
        $prefix = config('htmlcache.prefix');
        $locale = app()->getLocale();

        $page = str_replace('/', '_', trim($page, '/'));

        return "{$prefix}{$page}_{$locale}";
    }

    /**
     * Get all ignored routes that should not be affected by the caching. It will remove all
     * trailing slashes.
     *
     * @return array
     */
    protected function getIgnored()
    {
        return array_map(function ($value) {
            return trim($value, '/');
        }, config('htmlcache.ignored'));
    }
}
