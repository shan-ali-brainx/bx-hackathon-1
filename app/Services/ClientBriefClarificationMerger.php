<?php

namespace App\Services;

use App\Data\ClientBrief\ParsedAmbiguity;
use InvalidArgumentException;

class ClientBriefClarificationMerger
{
    /**
     * @param  array<int, ParsedAmbiguity|array{topic?: string, what_is_unclear?: string, suggested_question?: string}>  $ambiguities
     * @param  array<int, string>  $answers  Same order as $ambiguities
     */
    public function merge(string $baseBrief, array $ambiguities, array $answers, ?int $round = null): string
    {
        if (count($ambiguities) !== count($answers)) {
            throw new InvalidArgumentException('Ambiguity count must match answer count.');
        }

        if ($ambiguities === []) {
            return $baseBrief;
        }

        $label = $round !== null ? "Stakeholder clarifications (round {$round})" : 'Stakeholder clarifications';

        $block = ["\n\n--- {$label} ---\n"];

        foreach ($ambiguities as $i => $amb) {
            $topic = $this->resolveTopic($amb);
            $unclear = $this->resolveWhatIsUnclear($amb);
            $question = $this->resolveSuggestedQuestion($amb);
            $answer = $answers[$i];

            $block[] = sprintf(
                "Topic: %s\nWhat was unclear: %s\nClarifying question: %s\nAnswer: %s\n",
                $topic,
                $unclear,
                $question,
                $answer
            );
        }

        return rtrim($baseBrief)."\n".implode("\n", $block);
    }

    /**
     * @param  ParsedAmbiguity|array{topic?: string, what_is_unclear?: string, suggested_question?: string}  $amb
     */
    private function resolveTopic(ParsedAmbiguity|array $amb): string
    {
        if ($amb instanceof ParsedAmbiguity) {
            return $amb->topic;
        }

        return (string) ($amb['topic'] ?? 'Unknown topic');
    }

    /**
     * @param  ParsedAmbiguity|array{topic?: string, what_is_unclear?: string, suggested_question?: string}  $amb
     */
    private function resolveWhatIsUnclear(ParsedAmbiguity|array $amb): string
    {
        if ($amb instanceof ParsedAmbiguity) {
            return $amb->whatIsUnclear;
        }

        return (string) ($amb['what_is_unclear'] ?? '');
    }

    /**
     * @param  ParsedAmbiguity|array{topic?: string, what_is_unclear?: string, suggested_question?: string}  $amb
     */
    private function resolveSuggestedQuestion(ParsedAmbiguity|array $amb): string
    {
        if ($amb instanceof ParsedAmbiguity) {
            return $amb->suggestedQuestion;
        }

        return (string) ($amb['suggested_question'] ?? '');
    }
}
