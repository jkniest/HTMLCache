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

    /** @test */
    public function it_can_cache_the_result_of_a_response()
    {
        // When: The user visits the example page
        $response = $this->get('/example?test=Hello');

        // Then: They should see the word 'Hello'
        $response->assertStatus(200)->assertSee('Hello');

        // And: A new cache entry should exists
        $this->assertNotNull(cache('test_example_en'));

        // Also: The content should be valid
        $this->assertEquals('Example value: Hello', cache('test_example_en'));
    }

    /** @test */
    public function the_cache_is_loaded_when_visiting_a_page_twice()
    {
        // When: The user visits the example page with the attribute Hello
        $response = $this->get('/example?test=Hello');

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user visits the page again with another attribute
        $responseB = $this->get('/example?test=World');

        // Then: They should not see World, but Hello
        $responseB->assertDontSee('World');
        $responseB->assertSee('Hello');
    }

    /** @test */
    public function if_the_cache_is_disabled_the_normal_request_should_be_executed()
    {
        // Given: The HTMLCache is disabled
        Config::set('htmlcache.enabled', false);

        // When: The user visits the example page with the attribute Hello
        $response = $this->get('/example?test=Hello');

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user visits the page again with another attribute
        $responseB = $this->get('/example?test=World');

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');
    }

    /** @test */
    public function post_requests_are_ignored()
    {
        // When: The user sends a POST request to a given page
        $response = $this->post('/example', ['test' => 'Hello']);

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user makes another POST request to the same url
        $responseB = $this->post('/example', ['test' => 'World']);

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');
    }

    /** @test */
    public function delete_requests_are_ignored()
    {
        // When: The user sends a DELETE request to a given page
        $response = $this->delete('/example', ['test' => 'Hello']);

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user makes another DELETE request to the same url
        $responseB = $this->post('/example', ['test' => 'World']);

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');
    }

    /** @test */
    public function patch_requests_are_ignored()
    {
        // When: The user sends a PATCH request to a given page
        $response = $this->patch('/example', ['test' => 'Hello']);

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user makes another PATCH request to the same url
        $responseB = $this->patch('/example', ['test' => 'World']);

        // Then: They should not see Hello, but World
        $responseB->assertDontSee('Hello');
        $responseB->assertSee('World');
    }

    /** @test */
    public function put_requests_are_ignored()
    {
        // When: The user sends a PUT request to a given page
        $response = $this->put('/example', ['test' => 'Hello']);

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user makes another PUT request to the same url
        $responseB = $this->put('/example', ['test' => 'World']);

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

        // When: The user visits the page with an attribute: Hello
        $response = $this->get('/another?test=hello');

        // Then: He should see the Hello
        $response->assertStatus(200)->assertSee('hello');

        // When: The user visits the same page with another attribute: World
        $response = $this->get('/another?test=world');

        // Then: He should see the World but not hello
        $response->assertStatus(200)->assertSee('world');
        $response->assertDontSee('hello');
    }

    /** @test */
    public function it_will_ignore_routes_that_are_not_returning_a_200_status_code()
    {
        // When: The user sends a GET request to a page which returns a 500 status code
        $response = $this->get('/error?test=Hello');

        // Then: They should see the word 'Hello'
        $response->assertSee('Hello');

        // When: The user makes another request to the same url with another parameter
        $responseB = $this->get('/error?test=World');

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
        $this->assertNull(cache('test_example_en'));
    }

    /** @test */
    public function it_will_not_load_the_cache_of_a_page_if_the_error_bag_is_not_empty()
    {
        // Given: The user visited the example before
        $response = $this->get('/example?test=Hello');

        // And: This page was being cached
        $this->assertNotNull(cache('test_example_en'));

        // Also: The user should see Hello
        $response->assertSee('Hello');

        // Given: The user was earlier on the example page
        session()->setPreviousUrl(url('/example'));

        // When: The user sends a GET request to a page which will throw an validation exception
        $response = $this->get('/validation');

        // Then: The session should have an validation error
        $response->assertSessionHasErrors(['name']);

        // And: The user should be redirected to the example page again
        $response->assertRedirect('/example');

        // And: The user should not see the Hello
        $response->assertDontSee('Hello');
    }
}
