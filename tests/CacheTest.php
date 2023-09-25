<?php

namespace romanzipp\MailCheck\Tests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use romanzipp\MailCheck\Exceptions\DisposableMailException;

class CacheTest extends TestCase
{
    public function testResultIsCached()
    {
        config(['mailcheck.cache_checks' => true]);

        $checker = $this->mockChecker([
            new Response(200, [], json_encode(['status' => 200, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        $checker->allowedDomain('foobar');

        self::assertTrue(Cache::get('mailcheck_foobar'));
        self::assertNull(Cache::get('mailcheck_foo'));
    }

    public function testResultIsNotCached()
    {
        config(['mailcheck.cache_checks' => false]);

        $checker = $this->mockChecker([
            new Response(200, [], json_encode(['status' => 200, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
        ]);

        $checker->allowedDomain('foobar');

        self::assertNull(Cache::get('mailcheck_foobar'));
        self::assertNull(Cache::get('mailcheck_foo'));
    }

    public function testNoConsequentialRequestOnCachedResponse()
    {
        config(['mailcheck.cache_checks' => true]);

        $checker = $this->mockChecker([
            new Response(200, [], json_encode(['status' => 200, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
            new ClientException('Whoops', new Request('GET', 'domain/foobar'), new Response(400)),
        ]);

        $checker->allowedDomain('foobar');
        $checker->allowedDomain('foobar');

        self::assertTrue(Cache::get('mailcheck_foobar'));
    }

    public function testConsequentialRequestOnCachedResponse()
    {
        $this->expectException(DisposableMailException::class);
        $this->expectExceptionMessage('Invalid request');

        config(['mailcheck.cache_checks' => false]);

        $checker = $this->mockChecker([
            new Response(200, [], json_encode(['status' => 200, 'domain' => 'mailcheck.ai', 'mx' => true, 'disposable' => false, 'did_you_mean' => null])),
            new ClientException('Whoops', new Request('GET', 'domain/foobar'), new Response(400)),
        ]);

        $checker->allowedDomain('foobar');

        self::assertNull(Cache::get('mailcheck_foobar'));

        $checker->allowedDomain('foobar');
    }
}
