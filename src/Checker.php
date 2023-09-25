<?php

namespace romanzipp\MailCheck;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use romanzipp\MailCheck\Api\MailCheckApi;
use romanzipp\MailCheck\Api\Responses\DomainResponse;
use romanzipp\MailCheck\Enums\ApiIssue;
use romanzipp\MailCheck\Exceptions\DisposableMailException;
use romanzipp\MailCheck\Models\ValidatedDomain;

class Checker
{
    public int $remaining = 0;

    private bool $shouldStoreChecks;

    private bool $shouldCacheChecks;

    private int $cacheDuration;

    public MailCheckApi $api;

    // Fallbacks

    private ApiIssue $decisionRateLimit;

    private ApiIssue $decisionNoMx;

    private ApiIssue $decisionInvalid;

    public function __construct()
    {
        $this->api = new MailCheckApi(
            key: config('mailcheck.key')
        );

        $this->shouldStoreChecks = config('mailcheck.store_checks');
        $this->shouldCacheChecks = config('mailcheck.cache_checks');
        $this->cacheDuration = config('mailcheck.cache_duration');

        $this->decisionRateLimit = config('mailcheck.decision_rate_limit') ?? ApiIssue::EXCEPTION;
        $this->decisionNoMx = config('mailcheck.decision_no_mx') ?? ApiIssue::EXCEPTION;
        $this->decisionInvalid = config('mailcheck.decision_invalid') ?? ApiIssue::EXCEPTION;
    }

    private function decideOnIssue(DomainResponse $response): bool
    {
        if (false === $response->mx) {
            return match ($this->decisionNoMx) {
                ApiIssue::ALLOW => true,
                ApiIssue::DENY => false,
                ApiIssue::EXCEPTION => throw new DisposableMailException('MX entry invalid')
            };
        }

        if ($response->inRateLimit()) {
            return match ($this->decisionRateLimit) {
                ApiIssue::ALLOW => true,
                ApiIssue::DENY => false,
                ApiIssue::EXCEPTION => throw new DisposableMailException('Rate Limit exceeded')
            };
        }

        return match ($this->decisionInvalid) {
            ApiIssue::ALLOW => true,
            ApiIssue::DENY => false,
            ApiIssue::EXCEPTION => throw new DisposableMailException('Invalid request')
        };
    }

    /**
     * Check domain.
     *
     * @param string $domain
     *
     * @throws \romanzipp\MailCheck\Exceptions\DisposableMailException
     *
     * @return bool
     */
    public function allowedDomain(string $domain): bool
    {
        $cacheKey = "mailcheck_$domain";

        // Retreive from Cache if enabled

        if ($this->shouldCacheChecks && Cache::has($cacheKey)) {
            return (bool) Cache::get($cacheKey);
        }

        // Query API since we don't have a cacgcached response
        $response = $this->api->domain($domain);

        if ($response->hasIssue()) {
            return $this->decideOnIssue($response);
        }

        $isAllowed = ! $response->disposable;

        // Store in Cache if enabled

        if ($this->shouldCacheChecks) {
            Cache::put($cacheKey, $isAllowed, $this->cacheDuration);
        }

        // Store in Database or update Database query hits
        if ($this->shouldStoreChecks) {
            $this->storeResponse(
                domain: $domain,
                disposable: ! $isAllowed,
                mx: $response->mx
            );
        }

        return $isAllowed;
    }

    /**
     * Check email address.
     *
     * @param string $email
     *
     * @throws \romanzipp\MailCheck\Exceptions\DisposableMailException
     *
     * @return bool
     */
    public function allowedEmail(string $email): bool
    {
        if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        [$local, $domain] = explode('@', $email, 2);

        return $this->allowedDomain($domain);
    }

    private function storeResponse(string $domain, bool $disposable, bool $mx): void
    {
        if ( ! $this->shouldStoreChecks) {
            return;
        }

        ValidatedDomain::query()->firstOrCreate(
            [
                'domain' => $domain,
            ], [
                'mx' => $mx,
                'disposable' => $disposable,
                'last_queried' => Carbon::now(),
                'hits' => DB::raw('hits + 1'),
            ]
        );
    }
}
