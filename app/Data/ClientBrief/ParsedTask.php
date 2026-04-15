<?php

namespace App\Data\ClientBrief;

readonly class ParsedTask
{
    public function __construct(
        public string $title,
        public string $description,
        public ?string $acceptanceCriteria = null,
        public ?string $estimatedComplexity = null,
    ) {}
}
