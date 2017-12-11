<?php

namespace JKniest\Tests\Unit;

use JKniest\Tests\BaseTestCase;
use Illuminate\Support\Facades\Config;
use JKniest\HtmlCache\Http\Middleware\CacheHtml;

class CacheHtmlTest extends BaseTestCase
{
    /** @test */
    public function it_can_generate_a_cache_key_based_on_the_page_and_prefix()
    {
        // Given: The prefix is set to 'test_'
        Config::set('htmlcache.prefix', 'test_');

        // When: We generate the cache for the example page
        $key = (new MockedCacheHtml)->mGetCacheKey('example');

        // Then: The cache prefix should be:
        $this->assertEquals('test_example_en', $key);
    }

    /** @test */
    public function it_can_generate_a_cache_key_for_nested_resources()
    {
        // Given: The prefix is set to 'test_'
        Config::set('htmlcache.prefix', 'test_');

        // When: We generate the cache for the example/123/another page
        $key = (new MockedCacheHtml)->mGetCacheKey('example/123/another');

        // Then: The cache prefix should be:
        $this->assertEquals('test_example_123_another_en', $key);
    }

    /** @test */
    public function it_removes_trailing_slashes()
    {
        // Given: The prefix is set to 'test_'
        Config::set('htmlcache.prefix', 'test_');

        // When: We generate the cache for the /example/ page
        $key = (new MockedCacheHtml)->mGetCacheKey('/example/');

        // Then: The cache prefix should be:
        $this->assertEquals('test_example_en', $key);
    }

    /** @test */
    public function it_uses_the_current_language()
    {
        // Given: The prefix is set to 'test_'
        Config::set('htmlcache.prefix', 'test_');

        // Given: The language is 'de'
        app()->setLocale('de');

        // When: We generate the cache for the example page
        $key = (new MockedCacheHtml)->mGetCacheKey('example');

        // Then: The cache prefix should be:
        $this->assertEquals('test_example_de', $key);
    }

    /** @test */
    public function it_can_get_the_ignored_files()
    {
        // Given: A ignored route, named 'another'
        Config::set('htmlcache.ignored', [
            'another',
        ]);

        // When: We fetch the ignored routes
        $ignored = (new MockedCacheHtml)->mGetIgnored();

        // Then: It should be another
        $this->assertEquals(['another'], $ignored);
    }

    /** @test */
    public function it_removes_trailing_slashes_around_ignored_routes()
    {
        // Given: A ignored route, named '/another/'
        Config::set('htmlcache.ignored', [
            '/another/',
            'and/some/other/',
        ]);

        // When: We fetch the ignored routes
        $ignored = (new MockedCacheHtml)->mGetIgnored();

        // Then: It should be another and and/some/other (without the slashes)
        $this->assertEquals(['another', 'and/some/other'], $ignored);
    }
}

class MockedCacheHtml extends CacheHtml
{
    public function mGetCacheKey(string $page)
    {
        return $this->getCacheKey($page);
    }

    public function mGetIgnored()
    {
        return $this->getIgnored();
    }
}
