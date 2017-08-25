<?php

namespace JKniest\Tests\Unit;

use JKniest\HtmlCache\Http\Middleware\CacheHtml;
use Illuminate\Support\Facades\Config;
use JKniest\Tests\BaseTestCase;

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
}

class MockedCacheHtml extends CacheHtml
{
    public function mGetCacheKey(string $page)
    {
        return $this->getCacheKey($page);
    }
}