<?php

namespace JKniest\Tests\Feature;

use JKniest\Tests\BaseTestCase;
use Illuminate\Support\Facades\Config;

class CacheTest extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Config::set('htmlcache.prefix', 'test_');
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_cache_the_result_of_a_response()
    {
        // Given: The session data is 'Hello'
        session(['test' => 'Hello']);

        // When: The user visits the example page
        $path = 'example?test=Hello';
        $response = $this->get($path);

        // Then: They should see the word 'Hello'
        $response->assertStatus(200)->assertSee('Hello');

        // And: A new cache entry should exists
        $key = 'test_'.md5($path).'_en';
        $this->assertNotNull(cache($key));

        // Also: The content should be valid
        $this->assertEquals('Example value: Hello', cache($key)['content']);
    }

    /** @test */
    public function the_cache_is_loaded_when_visiting_a_page_twice()
    {
        // Given: A session data with the value 'Hello'
        session(['test' => 'Hello']);

        // When: The user visits the example page with the attribute Hello
        $response = $this->get('/example');

        // Then: They should see the word 'Hello' (because of the session value)
        $response->assertSee('Hello');

        // Given: The session data changed to 'World'
        session(['test' => 'World']);

        // When: The user visits the page again
        $responseB = $this->get('/example');

        // Then: They should not see World, but Hello
        $responseB->assertDontSee('World');
        $responseB->assertSee('Hello');
    }

    /** @test */
    public function if_the_cache_is_disabled_the_normal_request_should_be_executed()
    {
        // Given: The HTMLCache is disabled
        Config::set('htmlcache.enabled', false);

        // Given: The session value "Hello"
        session(['test' => 'Hello']);

        // When: The user visits the example page with the attribute Hello
        $response = $this->get('/example');

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // Given: The session value "World"
        session(['test' => 'World']);

        // When: The user visits the page again
        $responseB = $this->get('/example');

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');
    }

    /** @test */
    public function post_requests_are_ignored()
    {
        // When: The user sends a POST request to a given page
        $response = $this->post('/form', ['test' => 'Hello']);

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user makes another POST request to the same url
        $responseB = $this->post('/form', ['test' => 'World']);

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');
    }

    /** @test */
    public function delete_requests_are_ignored()
    {
        // When: The user sends a DELETE request to a given page
        $response = $this->delete('/form', ['test' => 'Hello']);

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user makes another DELETE request to the same url
        $responseB = $this->post('/form', ['test' => 'World']);

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');
    }

    /** @test */
    public function patch_requests_are_ignored()
    {
        // When: The user sends a PATCH request to a given page
        $response = $this->patch('/form', ['test' => 'Hello']);

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user makes another PATCH request to the same url
        $responseB = $this->patch('/form', ['test' => 'World']);

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');
    }

    /** @test */
    public function put_requests_are_ignored()
    {
        // When: The user sends a PUT request to a given page
        $response = $this->put('/form', ['test' => 'Hello']);

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user makes another PUT request to the same url
        $responseB = $this->put('/form', ['test' => 'World']);

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');
    }

    /** @test */
    public function it_can_ignore_specific_routes()
    {
        // Given: The route /another is ignored
        Config::set('htmlcache.ignored', [
            '/another',
        ]);

        // Given: The session value is 'hello'
        session(['test' => 'hello']);

        // When: The user visits the page
        $response = $this->get('/another');

        // Then: He should see the Hello
        $response->assertStatus(200)->assertSee('hello');

        // Given: The session value is 'world'
        session(['test' => 'world']);

        // When: The user visits the same page
        $response = $this->get('/another');

        // Then: He should see the World but not hello
        $response->assertStatus(200)->assertSee('world');
        $response->assertDontSee('hello');
    }

    /** @test */
    public function it_will_ignore_routes_that_are_not_returning_a_200_status_code()
    {
        // Given: The session text is 'Hello'
        session(['test' => 'Hello']);

        // When: The user sends a GET request to a page which returns a 500 status code
        $response = $this->get('/error');

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // Given: The session text is 'World'
        session(['test' => 'World']);

        // When: The user makes another request to the same url with another parameter
        $responseB = $this->get('/error');

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');

        // Also: The cache key should not have been generated (or at least with the null content)
        $this->assertNull(cache('test_error_en'));
    }

    /** @test */
    public function it_will_not_cache_any_pages_if_the_error_bag_is_not_empty()
    {
        // Given: The user was earlier on the example page
        session()->setPreviousUrl(url('/example'));

        // When: The user sends a GET request to a page which will throw an validation exception
        $response = $this->get('/validation');

        // Then: The session should have an validation error
        $response->assertSessionHasErrors(['name']);

        // And: The user should be redirected to the example page again
        $response->assertRedirect('/example');

        // And: The cache key should not have been generated (or at least with the null content)
        $this->assertNull(cache('test_'.md5('/example').'_en'));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_will_not_load_the_cache_of_a_page_if_the_error_bag_is_not_empty()
    {
        // Given: The session data is 'Hello'
        session(['test' => 'Hello']);

        // Given: The user visited the example before
        $response = $this->get('/example');

        // And: This page was being cached
        $md5 = md5('example');
        $this->assertNotNull(cache('test_'.$md5.'_en'));

        // Also: The user should see Hello
        $response->assertSee('Hello');

        // Given: The user was earlier on the example page
        session()->setPreviousUrl(url('/example'));

        // Given: The session data is 'World'
        session(['test' => 'World']);

        // When: The user sends a GET request to a page which will throw an validation exception
        $response = $this->get('/validation');

        // Then: The session should have an validation error
        $response->assertSessionHasErrors(['name']);

        // And: The user should be redirected to the example page again
        $response->assertRedirect('/example');

        // And: The user should not see the Hello, but world
        $response->assertDontSee('Hello');
    }

    /** @test */
    public function it_passes_the_headers_to_the_cached_response()
    {
        // When: The user visits the uncached example page
        $path = 'header';
        $response = $this->get($path);

        // Then: The page should have the header field
        $response->assertHeader('header', '2');

        // When: The user visits the cached example page again
        $path = 'header';
        $response = $this->get($path);

        // Then: The page should have the header field (but with the old value)
        $response->assertHeader('header', '2');
    }
}
