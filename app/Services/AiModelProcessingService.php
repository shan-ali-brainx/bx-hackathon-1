<?php

namespace App\Services;

use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Responses\AgentResponse;

class AiModelProcessingService
{
    /**
     * Run a single-turn text prompt through the configured AI provider and model.
     */
    public function process(
        string $input,
        ?string $instructions = null,
        ?string $model = null,
        ?string $provider = null,
    ): AgentResponse {
        $agent = new AnonymousAgent(
            $instructions ?? (string) config('ai.processing.instructions'),
            [],
            [],
        );

        return $agent->prompt(
            $input,
            [],
            $provider ?? config('ai.processing.provider'),
            $model ?? config('ai.text_model'),
        );
    }

    /**
     * Convenience helper returning plain text from the model response.
     */
    public function processToText(
        string $input,
        ?string $instructions = null,
        ?string $model = null,
        ?string $provider = null,
    ): string {
        return $this->process($input, $instructions, $model, $provider)->text;
    }
}
