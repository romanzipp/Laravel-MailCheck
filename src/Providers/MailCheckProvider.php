<?php

namespace romanzipp\MailCheck\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use romanzipp\MailCheck\Rules\DisposableEmail as ValidatorRule;

class MailCheckProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/../config/mailcheck.php' => config_path('mailcheck.php'),
        ], 'config');

        $this->loadMigrationsFrom(
            dirname(__DIR__) . '/../migrations'
        );

        Validator::extend('disposable', ValidatorRule::class . '@passes');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/../config/mailcheck.php', 'mailcheck'
        );
    }
}
