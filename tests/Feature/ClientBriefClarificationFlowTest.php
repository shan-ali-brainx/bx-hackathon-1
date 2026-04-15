<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Ai\StructuredAnonymousAgent;
use Tests\TestCase;

class ClientBriefClarificationFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_shows_clarification_form_when_ambiguities_exist(): void
    {
        StructuredAnonymousAgent::fake([
            [
                'brief_summary' => 'Client wants a dashboard.',
                'tasks' => [
                    [
                        'title' => 'Build dashboard shell',
                        'description' => 'Implement layout and navigation.',
                        'acceptance_criteria' => null,
                        'estimated_complexity' => 'medium',
                    ],
                ],
                'ambiguities' => [
                    [
                        'topic' => 'Dashboard visualizations',
                        'what_is_unclear' => 'Brief does not specify chart types or metrics.',
                        'suggested_question' => 'Should the dashboard include a graph view (e.g. time series)?',
                    ],
                ],
            ],
        ]);

        $brief = str_repeat('We need an analytics dashboard for our sales team. ', 2);

        $response = $this->post('/client-brief', [
            'brief' => $brief,
        ]);

        $response->assertOk()
            ->assertSee('Clarify these points', false)
            ->assertSee('Should the dashboard include a graph view', false)
            ->assertSee('Draft — may change after you clarify', false);
    }

    public function test_submits_clarifications_and_regenerates_tasks(): void
    {
        StructuredAnonymousAgent::fake([
            [
                'brief_summary' => 'Client wants a dashboard.',
                'tasks' => [
                    [
                        'title' => 'Build dashboard shell',
                        'description' => 'Implement layout and navigation.',
                        'acceptance_criteria' => null,
                        'estimated_complexity' => 'medium',
                    ],
                ],
                'ambiguities' => [
                    [
                        'topic' => 'Dashboard visualizations',
                        'what_is_unclear' => 'Brief does not specify chart types or metrics.',
                        'suggested_question' => 'Should the dashboard include a graph view?',
                    ],
                ],
            ],
            [
                'brief_summary' => 'Sales analytics dashboard with charts.',
                'tasks' => [
                    [
                        'title' => 'Implement graph view',
                        'description' => 'Add time-series charts per clarified requirements.',
                        'acceptance_criteria' => 'Graph view renders for selected date range.',
                        'estimated_complexity' => 'high',
                    ],
                ],
                'ambiguities' => [],
            ],
        ]);

        $brief = str_repeat('We need an analytics dashboard for our sales team. ', 2);

        $this->post('/client-brief', ['brief' => $brief])->assertOk();

        $response = $this->post('/client-brief/clarify', [
            'clarifications' => [
                'Yes—include a time-series graph for revenue and a filter by date range.',
            ],
        ]);

        $response->assertOk()
            ->assertSee('Implement graph view', false)
            ->assertDontSee('Draft — may change after you clarify', false);
    }

    public function test_clarify_rejects_request_when_session_missing(): void
    {
        $response = $this->from('/client-brief')->post('/client-brief/clarify', [
            'clarifications' => ['Something'],
        ]);

        $response->assertSessionHasErrors('clarifications');
    }
}
