<?php

namespace Tests\Feature;

use App\Services\ClientBriefParser;
use Laravel\Ai\StructuredAnonymousAgent;
use RuntimeException;
use Tests\TestCase;

class GenerateTasksApiTest extends TestCase
{
    public function test_returns_structured_tasks_on_success(): void
    {
        StructuredAnonymousAgent::fake([
            [
                'brief_summary' => 'Summary.',
                'tasks' => [
                    [
                        'title' => 'Ship feature',
                        'description' => 'Do the work described in the brief.',
                        'acceptance_criteria' => null,
                        'estimated_complexity' => 'low',
                    ],
                ],
                'ambiguities' => [],
            ],
        ]);

        $brief = str_repeat('We need a secure login and dashboard for our team. ', 2);

        $response = $this->postJson('/api/generate-tasks', [
            'brief' => $brief,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tasks generated successfully.')
            ->assertJsonPath('data.tasks.0.title', 'Ship feature');
    }

    public function test_validates_brief_is_required(): void
    {
        $response = $this->postJson('/api/generate-tasks', [], [
            'Accept' => 'application/json',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['brief']);
    }

    public function test_validates_brief_minimum_length(): void
    {
        $response = $this->postJson('/api/generate-tasks', [
            'brief' => 'Too short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['brief']);
    }

    public function test_returns_error_when_generation_fails(): void
    {
        $this->mock(ClientBriefParser::class, function ($mock): void {
            $mock->shouldReceive('parse')
                ->once()
                ->andThrow(new RuntimeException('provider unavailable'));
        });

        $brief = str_repeat('We need a secure login and dashboard for our team. ', 2);

        $response = $this->postJson('/api/generate-tasks', [
            'brief' => $brief,
        ]);

        $response->assertStatus(503)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Task generation failed. Please try again later.');
    }
}
