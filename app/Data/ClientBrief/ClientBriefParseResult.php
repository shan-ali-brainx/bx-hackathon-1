<?php

namespace App\Data\ClientBrief;

readonly class ClientBriefParseResult
{
    /**
     * @param  array<int, ParsedTask>  $tasks
     * @param  array<int, ParsedAmbiguity>  $ambiguities
     */
    public function __construct(
        public array $tasks,
        public array $ambiguities,
        public ?string $briefSummary = null,
    ) {}

    /**
     * @return array{tasks: array<int, array<string, mixed>>, ambiguities: array<int, array<string, mixed>>, brief_summary: ?string}
     */
    public function toArray(): array
    {
        return [
            'brief_summary' => $this->briefSummary,
            'tasks' => array_map(fn (ParsedTask $t) => [
                'title' => $t->title,
                'description' => $t->description,
                'acceptance_criteria' => $t->acceptanceCriteria,
                'estimated_complexity' => $t->estimatedComplexity,
            ], $this->tasks),
            'ambiguities' => array_map(fn (ParsedAmbiguity $a) => [
                'topic' => $a->topic,
                'what_is_unclear' => $a->whatIsUnclear,
                'suggested_question' => $a->suggestedQuestion,
            ], $this->ambiguities),
        ];
    }
}
