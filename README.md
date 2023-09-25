# Laravel MailCheck.ai

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/laravel-mailcheck.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-mailcheck)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/laravel-mailcheck.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-mailcheck)
[![License](https://img.shields.io/packagist/l/romanzipp/laravel-mailcheck.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-mailcheck)
[![GitHub Build Status](https://img.shields.io/github/actions/workflow/status/romanzipp/Laravel-MailCheck/tests.yml?label=tests&branch=master&style=flat-square)](https://github.com/romanzipp/Laravel-MailCheck/actions)

A Laravel Wrapper for the [MailCheck.ai](https://www.mailcheck.ai) disposable email API made by [@tompec](https://github.com/tompec).

## Features

- Query the [MailCheck.ai](https://www.mailcheck.ai) API for disposable Emails & Domains
- Cache responses
- Store requested domains in database

## Upgrading

1. Update package name
2. Update config file name from `config/validator-pizza.php` → `config/mailcheck.php`

## Installation

```
composer require romanzipp/laravel-mailcheck
```

## Configuration

Copy configuration to your project:

```
php artisan vendor:publish --provider="romanzipp\MailCheck\Providers\MailCheckProvider"
```

Run the migration:

```
php artisan migrate
```

Change the config to your desired settings:

```php
return [

    // Database storage enabled
    'store_checks' => true,

    // Database table name
    'checks_table' => 'validator_pizza',

    // Cache enabled (recommended)
    'cache_checks' => true,

    // Duration in minutes to keep the query in cache
    'cache_duration' => 30,

    // Determine which decision should be given if the rate limit is exceeded [allow / deny]
    'decision_rate_limit' => 'allow',

    // Determine which decision should be given if the domain has no MX DNS record [allow / deny]
    'decision_no_mx' => 'allow',

    // Makes use of the API key
    'key' => env('VALIDATOR_PIZZA_KEY'),
];
```

## Usage

#### Controller Validation

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function handleEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|disposable',
        ]);

        // ...
    }
}
```

#### Standalone

```php
$checker = new \romanzipp\MailCheck\Checker;

// Validate Email
$validEmail = $checker->allowedEmail('ich@ich.wtf');

// Validate Domain
$validDomain = $checker->allowedDomain('ich.wtf');
```
