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

## Migrating from Validator.Pizza

1. Update **package name**    
    `composer remove romanzipp/laravel-validator-pizza`    
    `composer require romanzipp/laravel-mailcheck`
2. Update **config file** name    
    `config/mailcheck.php` → `config/mailcheck.php`
3. Rename **code references**    
    `romanzipp\ValidatorPizza\` → `romanzipp\MailCheck\`
4. Rename **rule**    
    `validator_pizza` → `disposable`
5. The default new **table name** will be `mailcheck_checks`. If you want to keep the previous `validator_pizza` table name change the entry in your config file.

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
