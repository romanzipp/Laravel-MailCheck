<?php

namespace romanzipp\MailCheck\Api\Responses;

class DomainResponse
{
    public function __construct(
        public int $status,
        public ?string $domain = null,
        public ?bool $mx = null,
        public ?bool $disposable = null,
        public ?string $did_you_mean = null
    ) {
    }

    public function hasIssue(): bool
    {
        return $this->inRateLimit() || false === $this->mx || 400 === $this->status;
    }

    public function inRateLimit(): bool
    {
        return 429 === $this->status;
    }
}
