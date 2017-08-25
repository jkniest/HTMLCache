<?php

namespace JKniest\Tests\Feature;

use Illuminate\Support\Facades\Config;
use JKniest\Tests\BaseTestCase;

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
        $this->assertEquals('Example value: Hello', cache('test_example_en')->content());
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
}