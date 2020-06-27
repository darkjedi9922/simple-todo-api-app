<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommonTest extends TestCase
{
    public function testOnlyApiIsSupported()
    {
        $this->get('/')->assertStatus(404);
    }

    public function testNonExistencePagesReturnsNotFound()
    {
        $this->get('/non-existence/page')->assertStatus(404); // For WEB routes
        $this->get('/api/non-existence/page')->assertStatus(404); // For API routes
    }
}
