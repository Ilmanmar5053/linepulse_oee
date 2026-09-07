<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    public function test_oee_kpi_endpoint_returns_json_data(): void
    {
        $response = $this->getJson('/api/v1/dashboard/oee-kpi');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'oee',
                    'availability',
                    'performance',
                    'quality',
                    'target_oee',
                    'status',
                ],
            ]);
    }
}
