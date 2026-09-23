<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationHealthTest extends TestCase
{
    public function test_health_endpoint_does_not_require_an_acceptance_profile(): void
    {
        $this->get('/up')->assertOk();
    }
}
