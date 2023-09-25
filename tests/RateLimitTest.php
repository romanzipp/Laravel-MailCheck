<?php

namespace romanzipp\MailCheck\Tests;

use GuzzleHttp\Psr7\Response;
use romanzipp\MailCheck\Enums\ApiIssue;
use romanzipp\MailCheck\Exceptions\DisposableMailException;

class RateLimitTest extends TestCase
{
    public function testException()
    {
        $this->expectException(DisposableMailException::class);
        $this->expectExceptionMessage('Rate Limit exceeded');

        config(['mailcheck.decision_rate_limit' => ApiIssue::EXCEPTION]);

        $checker = $this->mockChecker([
            new Response(429, [], json_encode(['status' => 429, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        $checker->allowedDomain('foobar');
    }

    public function testAllow()
    {
        config(['mailcheck.decision_rate_limit' => ApiIssue::DENY]);

        $checker = $this->mockChecker([
            new Response(429, [], json_encode(['status' => 429, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        self::assertFalse($checker->allowedDomain('foobar'));
    }

    public function testDeny()
    {
        config(['mailcheck.decision_rate_limit' => ApiIssue::ALLOW]);

        $checker = $this->mockChecker([
            new Response(429, [], json_encode(['status' => 429, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        self::assertTrue($checker->allowedDomain('foobar'));
    }
}
