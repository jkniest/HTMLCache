<?php

namespace JKniest\HtmlCache\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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
        if ($this->isEnabled($request)) {
            $content = $this->getContent($request, $next);

            if ($content === null) {
                return $next($request);
            }

            return response($content);
        }

        return $next($request);
    }

    /**
     * Generate the cache key for a given page. It will have the following syntax:
     * PREFIX_PAGE_LOCALE_USERID
     *
     * The prefix is configurable in the config file. The page is the current page the user
     * is visiting and the locale is the current locale (default: en).
     *
     * The user id is only set if the configuration value (htmlcache.user_specific) is set
     * to true.
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

        if (config('htmlcache.user_specific')) {
            $id = Auth::check() ? Auth::id() : -1;

            return "{$prefix}{$page}_{$locale}_{$id}";
        }

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
            if ($value === '/') {
                return $value;
            }

            return trim($value, '/');
        }, config('htmlcache.ignored'));
    }

    /**
     * Is the caching system enabled for this specific page?
     *
     * It is enabled when:
     * - The http method is GET
     * - The caching is enabled in config
     * - And the route is not ignored
     *
     * @param Request $request The incoming request
     *
     * @return bool
     */
    protected function isEnabled(Request $request)
    {
        return (
            $request->method() === 'GET' &&
            config('htmlcache.enabled') &&
            !$request->is(... $this->getIgnored())
        );
    }

    /**
     * Get the original or the cached response. If there is now cached version for the
     * current page it will run the full request cycle and cache the given response, if
     * the returned status code is equals to 200.
     *
     * Otherwise, if there is a cache version, it will not run the whole request cycle
     * and simply return the cached html / response.
     *
     * @param Request  $request The incoming request
     * @param callable $next    The next middleware
     *
     * @return null|string
     */
    protected function getContent(Request $request, callable $next)
    {
        $key = $this->getCacheKey($request->path());
        $time = config('htmlcache.minutes');

        $content = Cache::remember($key, $time, function () use ($next, $request) {
            $response = $next($request);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            return $response->getContent();
        });

        return $content;
    }
}
