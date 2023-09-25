<?php

namespace romanzipp\MailCheck\Tests;

use GuzzleHttp\Psr7\Response;
use romanzipp\MailCheck\Enums\ApiIssue;
use romanzipp\MailCheck\Exceptions\DisposableMailException;

class NoMxTest extends TestCase
{
    public function testException()
    {
        $this->expectException(DisposableMailException::class);
        $this->expectExceptionMessage('MX entry invalid');

        config(['mailcheck.decision_no_mx' => ApiIssue::EXCEPTION]);

        $checker = $this->mockChecker([
            new Response(200, [], json_encode(['status' => 200, 'domain' => 'mailcheck.ai', 'mx' => false, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        $checker->allowedDomain('foobar');
    }

    public function testAllow()
    {
        config(['mailcheck.decision_no_mx' => ApiIssue::DENY]);

        $checker = $this->mockChecker([
            new Response(200, [], json_encode(['status' => 200, 'domain' => 'mailcheck.ai', 'mx' => false, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        self::assertFalse($checker->allowedDomain('foobar'));
    }

    public function testDeny()
    {
        config(['mailcheck.decision_no_mx' => ApiIssue::ALLOW]);

        $checker = $this->mockChecker([
            new Response(200, [], json_encode(['status' => 200, 'domain' => 'mailcheck.ai', 'mx' => false, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        self::assertTrue($checker->allowedDomain('foobar'));
    }
}
