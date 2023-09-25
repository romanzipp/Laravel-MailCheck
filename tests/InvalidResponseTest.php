<?php

namespace romanzipp\MailCheck\Tests;

use GuzzleHttp\Psr7\Response;
use romanzipp\MailCheck\Enums\ApiIssue;
use romanzipp\MailCheck\Exceptions\DisposableMailException;

class InvalidResponseTest extends TestCase
{
    public function testException()
    {
        $this->expectException(DisposableMailException::class);
        $this->expectExceptionMessage('Invalid request');

        config(['mailcheck.decision_invalid' => ApiIssue::EXCEPTION]);

        $checker = $this->mockChecker([
            new Response(400, [], json_encode(['status' => 400, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        $checker->allowedDomain('foobar');
    }

    public function testAllow()
    {
        config(['mailcheck.decision_invalid' => ApiIssue::DENY]);

        $checker = $this->mockChecker([
            new Response(400, [], json_encode(['status' => 400, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        self::assertFalse($checker->allowedDomain('foobar'));
    }

    public function testDeny()
    {
        config(['mailcheck.decision_invalid' => ApiIssue::ALLOW]);

        $checker = $this->mockChecker([
            new Response(400, [], json_encode(['status' => 400, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        self::assertTrue($checker->allowedDomain('foobar'));
    }
}
