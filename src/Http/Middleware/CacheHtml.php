<?php

namespace JKniest\HtmlCache\Http\Middleware;

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
    public function getCacheKey(string $page)
    {
        $prefix = config('htmlcache.prefix');
        $locale = app()->getLocale();

        $page = str_replace('/', '_', trim($page, '/'));

        return "{$prefix}{$page}_{$locale}";
    }
}