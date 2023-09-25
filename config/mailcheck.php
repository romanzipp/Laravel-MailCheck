<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database storing
    |--------------------------------------------------------------------------
    |
    | Decide wether the requested doamins & email addresses should be
    | stored to the database.
    |
    */

    // Database storage enabled
    'store_checks' => true,

    // Database table name (previously: validator_pizza)
    'checks_table' => 'mailcheck_checks',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | It is recommended to cache requests due to API rate limitations.
    |
    */

    // Cache enabled (recommended)
    'cache_checks' => true,

    // Duration in minutes to keep the query in cache
    'cache_duration' => 30,

    /*
    |--------------------------------------------------------------------------
    | Advanced
    |--------------------------------------------------------------------------
    |
    | Advanced configuration
    |
    | Available decision types:
    |   romanzipp\MailCheck\Enums\ApiIssue::ALLOW
    |   romanzipp\MailCheck\Enums\ApiIssue::DENY
    |   romanzipp\MailCheck\Enums\ApiIssue::EXCEPTION
    |
    */

    // Configure what should happen if the Rate Limit is exceeded
    'decision_rate_limit' => \romanzipp\MailCheck\Enums\ApiIssue::EXCEPTION,

    // Configure what should happen if the domain has no valid MX DNS entries
    'decision_no_mx' => \romanzipp\MailCheck\Enums\ApiIssue::DENY,

    // Configure what should happen if the request is invalid for another reason
    'decision_invalid' => \romanzipp\MailCheck\Enums\ApiIssue::DENY,

    // Makes use of the API key
    'key' => env('MAILCHECK_KEY'),
];
