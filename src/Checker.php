<?php

namespace romanzipp\MailCheck;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use romanzipp\MailCheck\Models\ValidatedDomain;

class Checker
{
    public int $remaining = 0;

    public bool $fromCache;

    private bool $storeChecks;

    private bool $cacheChecks;

    private int $cacheDuration;

    private string $decisionRateLimit;

    private string $decisionNoMx;

    private Client $client;

    private string $key;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://www.mailcheck.ai',
        ]);

        $this->storeChecks = config('mailcheck.store_checks');
        $this->cacheChecks = config('mailcheck.cache_checks');
        $this->cacheDuration = config('mailcheck.cache_duration');
        $this->decisionRateLimit = config('mailcheck.decision_rate_limit');
        $this->decisionNoMx = config('mailcheck.decision_no_mx');
        $this->key = config('mailcheck.key');
    }

    /**
     * Check domain.
     *
     * @param string $domain
     *
     * @return bool
     */
    public function allowedDomain(string $domain): bool
    {
        $cacheKey = 'mailcheck_' . $domain;
        $data = null;

        // Retreive from Cache if enabled

        if ($this->cacheChecks && Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);

            $this->fromCache = true;
        }

        if ( ! $this->fromCache) {
            $response = $this->query($domain);

            // The email address is invalid
            if (400 == $response->status) {
                return false;
            }

            // Rate limit exceeded
            if (429 == $response->status) {
                return 'allow' === $this->decisionRateLimit ? true : false;
            }

            if (200 != $response->status) {
                return false;
            }

            $data = $response;
        }

        // Store in Cache if enabled

        if ($this->cacheChecks && ! $this->fromCache) {
            Cache::put($cacheKey, $data, $this->cacheDuration);
        }

        // Store in Database or update Database query hits

        if ($this->storeChecks) {
            $this->storeResponse($data);
        }

        return $this->decideIsValid($data);
    }

    /**
     * Check email address.
     *
     * @param string $email
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

    /**
     * Query the API.
     *
     * @param string $domain
     *
     * @throws \GuzzleHttp\Exception\ClientException
     *
     * @return \stdClass API response data
     */
    private function query(string $domain): \stdClass
    {
        $uri = '/domain/' . strtolower($domain);

        if ($this->key) {
            $uri .= '?key=' . $this->key;
        }

        $request = new Request('GET', $uri, [
            'Accept' => 'application/json',
        ]);

        try {
            $response = $this->client->send($request);
        } catch (RequestException $e) {
            return (object) [
                'status' => $e->getResponse()->getStatusCode(),
            ];
        }

        $data = json_decode($response->getBody());

        return (object) [
            'status' => 200,
            'domain' => $data->domain,
            'mx' => optional($data)->mx ?? false,
            'disposable' => optional($data)->disposable ?? false,
        ];
    }

    private function storeResponse(\stdClass $data): void
    {
        $this->remaining = $data->remaining_requests ?? 0;

        if ($this->storeChecks) {
            /** @var \romanzipp\MailCheck\Models\ValidatedDomain $check */
            $check = ValidatedDomain::query()->firstOrCreate(
                [
                    'domain' => $data->domain,
                ], [
                    'mx' => $data->mx,
                    'disposable' => $data->disposable,
                    'last_queried' => Carbon::now(),
                ]
            );

            if ( ! $check->wasRecentlyCreated) {
                ++$check->hits;
                $check->last_queried = Carbon::now();

                $check->save();
            }
        }
    }

    /**
     * Decide wether the given data represents a valid domain.
     *
     * @param \stdClass $data
     *
     * @return bool
     */
    private function decideIsValid(\stdClass $data): bool
    {
        if ('deny' == $this->decisionNoMx && true !== optional($data)->mx) {
            return false;
        }

        return false === optional($data)->disposable;
    }
}
