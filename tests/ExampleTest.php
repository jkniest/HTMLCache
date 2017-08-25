<?php

namespace JKniest\Tests;

class ExampleTest extends BaseTestCase
{
    /** @test */
    public function test_route()
    {
        $this->get('/example?test=Hello')
            ->assertStatus(200)
            ->assertSee('Hello');
    }
}