<?php

namespace Tests\Feature;

use App\Data\ClientBrief\ClientBriefParseResult;
use App\Services\ClientBriefParser;
use Laravel\Ai\StructuredAnonymousAgent;
use Tests\TestCase;

/**
 * Example flow: send unstructured client copy to the parser, which calls the
 * Laravel AI SDK (structured output). Here we fake the model so tests run
 * offline; in production, set OPENAI_API_KEY and remove the fake.
 */
class ClientBriefParserTest extends TestCase
{
    public function test_parses_brief_into_tasks_and_ambiguities(): void
    {
        // 1) Fake the next structured model response (shape matches ClientBriefParser schema).
        StructuredAnonymousAgent::fake([
            [
                'brief_summary' => 'Client wants a marketing landing page with a lead form.',
                'tasks' => [
                    [
                        'title' => 'Implement marketing landing page',
                        'description' => 'Build a public landing page consistent with the brief: headline, sections, responsive layout.',
                        'acceptance_criteria' => 'Page loads on mobile and desktop; primary CTA visible above the fold.',
                        'estimated_complexity' => 'medium',
                    ],
                    [
                        'title' => 'Add lead capture form',
                        'description' => 'Collect name, email, and company; store submissions securely.',
                        'acceptance_criteria' => 'Validates email; shows success state after submit.',
                        'estimated_complexity' => 'medium',
                    ],
                ],
                'ambiguities' => [
                    [
                        'topic' => 'Brand & styling',
                        'what_is_unclear' => 'Brief says "on-brand" but no logo, colors, or typography provided.',
                        'suggested_question' => 'Can you share brand guidelines or reference designs we should match?',
                    ],
                    [
                        'topic' => 'Launch date',
                        'what_is_unclear' => 'Urgency is mentioned ("ASAP") but no fixed date or milestone.',
                        'suggested_question' => 'What is the must-ship date and are there any hard marketing deadlines?',
                    ],
                ],
            ],
        ]);

        // 2) Raw brief from the client (messy on purpose).
        $rawBrief = <<<'BRIEF'
        Hi — we need our new product landing ASAP, should feel really on-brand.
        Definitely need a form so leads can reach us. More details soon.
        BRIEF;

        // 3) Parse: service → AI SDK → structured result.
        /** @var ClientBriefParseResult $result */
        $result = app(ClientBriefParser::class)->parse($rawBrief);

        // 4) Assert structured task list and human-review flags.
        $this->assertSame('Client wants a marketing landing page with a lead form.', $result->briefSummary);
        $this->assertCount(2, $result->tasks);
        $this->assertSame('Implement marketing landing page', $result->tasks[0]->title);
        $this->assertNotEmpty($result->tasks[0]->description);
        $this->assertCount(2, $result->ambiguities);
        $this->assertStringContainsString('brand', strtolower($result->ambiguities[0]->topic));

        // Serializable shape for APIs / queues / logs:
        $payload = $result->toArray();
        $this->assertArrayHasKey('tasks', $payload);
        $this->assertArrayHasKey('ambiguities', $payload);
    }
}
