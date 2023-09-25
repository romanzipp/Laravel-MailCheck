<?php

namespace romanzipp\MailCheck\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use romanzipp\MailCheck\Api\Responses\DomainResponse;

class MailCheckApi
{
    public Client $client;

    public function __construct(
        string $key = null
    ) {
        $this->client = new Client([
            'base_uri' => 'https://api.mailcheck.ai/',
            'headers' => [
                'Accept' => 'application/json',
                ...$key ? [
                    'Authorization' => "Bearer $key",
                ] : [],
            ],
        ]);
    }

    public function domain(string $domain): DomainResponse
    {
        try {
            $response = $this->client->get("domain/$domain");

            $data = json_decode($response->getBody());

            return new DomainResponse(
                status: $data->status,
                domain: $data->domain,
                mx: $data->mx,
                disposable: $data->disposable,
                did_you_mean: $data->did_you_mean,
            );
        } catch (ClientException $exception) {
            return new DomainResponse(
                status: $exception->getResponse()->getStatusCode()
            );
        }
    }
}
