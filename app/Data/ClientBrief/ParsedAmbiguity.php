<?php

namespace App\Data\ClientBrief;

readonly class ParsedAmbiguity
{
    public function __construct(
        public string $topic,
        public string $whatIsUnclear,
        public string $suggestedQuestion,
    ) {}
}
