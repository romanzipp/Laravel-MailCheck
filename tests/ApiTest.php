<?php

namespace romanzipp\MailCheck\Tests;

use romanzipp\MailCheck\Checker;

class ApiTest extends TestCase
{
    public function testAllowedDomain()
    {
        $checker = (new Checker())->allowedDomain('mailcheck.ai');

        $this->assertTrue($checker);
    }

    public function testDisposableDomain()
    {
        $checker = (new Checker())->allowedDomain('mailinator.com');

        $this->assertFalse($checker);
    }

    public function testInvalidDomain()
    {
        $checker = (new Checker())->allowedDomain('t.t');

        $this->assertFalse($checker);
    }
}
