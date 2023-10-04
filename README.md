# Laravel MailCheck.ai

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/laravel-mailcheck.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-mailcheck)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/laravel-mailcheck.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-mailcheck)
[![License](https://img.shields.io/packagist/l/romanzipp/laravel-mailcheck.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-mailcheck)
[![GitHub Build Status](https://img.shields.io/github/actions/workflow/status/romanzipp/Laravel-MailCheck/tests.yml?label=tests&branch=master&style=flat-square)](https://github.com/romanzipp/Laravel-MailCheck/actions)

A Laravel Wrapper for the [MailCheck.ai](https://www.mailcheck.ai) disposable email API made by [@tompec](https://github.com/tompec).

## ✨ Migrating from Validator.Pizza

This package was previously called "Validator.Pizza". See the following guide if you want to migrate your preivous installation.

<details>
<summary>Migration Guide</summary>

###  **Package name**

```sh
composer remove romanzipp/laravel-validator-pizza    
composer require romanzipp/laravel-mailcheck
```

### **Config file**

Update the configuration file name.

```diff
- config/mailcheck.php
+ config/mailcheck.php
```

### **Code references**

```diff
- romanzipp\ValidatorPizza\
+ romanzipp\MailCheck\
```

### **Rule**

```diff
- 'email' => 'required|email|validator_pizza',
+ 'email' => 'required|email|disposable', 
```

### Table name

The default new **table name** will be `mailcheck_checks`. If you want to keep the previous `validator_pizza` table name change the entry in your config file.

```diff
- validator_pizza
+ mailcheck_checks
```

</details>

## Features

- Query the [MailCheck.ai](https://www.mailcheck.ai) API for disposable Emails & Domains
- Cache responses
- Store requested domains in database

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

// Validate domain
$validDomain = $checker->allowedDomain('ich.wtf');

// Validate mail address (uses domain check endpoint internally)
$validEmail = $checker->allowedEmail('ich@ich.wtf');
```

## Advanced Usage

You can make your disposable checks more hard or loose by configuring the edge case behavior.
There are 3 possible outcomes to set: 

- `romanzipp\MailCheck\Enums\ApiIssue::ALLOW` - allow the domain/mail
- `romanzipp\MailCheck\Enums\ApiIssue::DENY` - deny the chekechecked domain/mail
- `romanzipp\MailCheck\Enums\ApiIssue::EXCEPTION` - throw a [`DisposableMailException`](src/Exceptions/DisposableMailException)

### Rate Limit exceeded

```php
return [
    // ... 
    'decision_rate_limit' => \romanzipp\MailCheck\Enums\ApiIssue::EXCEPTION,
];
```

### No MX DNS records present

There is no MX DNS entry present on the checked domain which means they can not receive any messages.

```php
return [
    // ... 
    'decision_no_mx' => \romanzipp\MailCheck\Enums\ApiIssue::EXCEPTION,
];
```

### Invalid request

```php
return [
    // ... 
    'decision_invalid' => \romanzipp\MailCheck\Enums\ApiIssue::EXCEPTION,
];
```
