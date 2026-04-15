<?php

namespace App\Services;

use App\Data\ClientBrief\ClientBriefParseResult;
use App\Data\ClientBrief\ParsedAmbiguity;
use App\Data\ClientBrief\ParsedTask;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Laravel\Ai\StructuredAnonymousAgent;

class ClientBriefParser
{
    public function parse(
        string $rawBrief,
        ?string $provider = null,
        ?string $model = null,
    ): ClientBriefParseResult {
        $agent = new StructuredAnonymousAgent(
            $this->systemInstructions(),
            [],
            [],
            fn (JsonSchema $schema) => $this->structuredOutputSchema($schema),
        );

        $prompt = $this->wrapBriefForModel($rawBrief);

        /** @var StructuredAgentResponse $response */
        $response = $agent->prompt(
            $prompt,
            [],
            $provider ?? config('ai.processing.provider'),
            $model ?? config('ai.text_model'),
        );

        return $this->mapResponseToResult($response->structured);
    }

    protected function systemInstructions(): string
    {
        return <<<'INSTRUCTIONS'
You are an expert delivery lead. You receive a raw, possibly messy client brief.

Your goals:
1. Extract a concise list of actionable engineering or delivery tasks. Each task must have a clear title and a concrete description of what to do. Prefer crisp acceptance criteria when the brief implies them.
2. Identify ambiguities, missing requirements, or conflicting statements. For each, propose one specific clarifying question a human should ask the client.

Use only information supported by the brief; do not invent factual product constraints. If something is unclear, it belongs in ambiguities, not as a false certainty in tasks.

If the brief includes a block titled "--- Stakeholder clarifications ---" with explicit Q/A pairs, treat those answers as authoritative new requirements and fold them into tasks; only list remaining gaps as ambiguities.

Respond using the required structured format only.
INSTRUCTIONS;
    }

    protected function wrapBriefForModel(string $rawBrief): string
    {
        return <<<TEXT
Parse the following client brief into structured tasks and ambiguities.

--- CLIENT BRIEF (verbatim) ---
{$rawBrief}
--- END BRIEF ---
TEXT;
    }

    /**
     * @return array<string, Type>
     */
    protected function structuredOutputSchema(JsonSchema $schema): array
    {
        /*
         * OpenAI json_schema with strict: true requires every key in `properties` to appear in `required`.
         * Laravel's serializer only emits `required` for fields that call ->required(); nullable optional
         * fields must still use ->required() so the key is always present (value may be null).
         * Avoid enum() + nullable() on the same string — the serialized schema is invalid for strict mode.
         */
        return [
            'brief_summary' => $schema->string()
                ->nullable()
                ->required()
                ->description('One or two sentences summarizing the brief'),

            'tasks' => $schema->array()
                ->items($schema->object([
                    'title' => $schema->string()
                        ->required()
                        ->description('Short, actionable task title'),
                    'description' => $schema->string()
                        ->required()
                        ->description('What to do, in plain language'),
                    'acceptance_criteria' => $schema->string()
                        ->nullable()
                        ->required()
                        ->description('Optional measurable criteria when inferable; use null if none'),
                    'estimated_complexity' => $schema->string()
                        ->enum(['low', 'medium', 'high'])
                        ->required()
                        ->description('Rough sizing; use low if unknown'),
                ]))
                ->required(),

            'ambiguities' => $schema->array()
                ->items($schema->object([
                    'topic' => $schema->string()
                        ->required()
                        ->description('What area is unclear'),
                    'what_is_unclear' => $schema->string()
                        ->required()
                        ->description('Why it is ambiguous or what is missing'),
                    'suggested_question' => $schema->string()
                        ->required()
                        ->description('One question for a human to ask the client'),
                ]))
                ->required(),
        ];
    }

    /**
     * @param  array<string, mixed>  $structured
     */
    protected function mapResponseToResult(array $structured): ClientBriefParseResult
    {
        $tasks = [];
        foreach ($structured['tasks'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $tasks[] = new ParsedTask(
                title: (string) ($row['title'] ?? 'Untitled task'),
                description: (string) ($row['description'] ?? ''),
                acceptanceCriteria: isset($row['acceptance_criteria']) ? (string) $row['acceptance_criteria'] : null,
                estimatedComplexity: isset($row['estimated_complexity']) ? (string) $row['estimated_complexity'] : null,
            );
        }

        $ambiguities = [];
        foreach ($structured['ambiguities'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $ambiguities[] = new ParsedAmbiguity(
                topic: (string) ($row['topic'] ?? 'Unknown topic'),
                whatIsUnclear: (string) ($row['what_is_unclear'] ?? ''),
                suggestedQuestion: (string) ($row['suggested_question'] ?? ''),
            );
        }

        $summary = $structured['brief_summary'] ?? null;
        if ($summary !== null) {
            $summary = (string) $summary;
        }

        return new ClientBriefParseResult(
            tasks: $tasks,
            ambiguities: $ambiguities,
            briefSummary: $summary,
        );
    }
}
