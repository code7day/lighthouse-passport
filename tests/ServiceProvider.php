<?php

namespace Code7day\LighthousePassportLogin\Tests;

use Laravel\Passport\Passport;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/../tests/migrations'));
        Passport::routes();
        Passport::loadKeysFrom(__DIR__.'/storage/');
        config()->set('lighthouse.route.middleware', [
            \Nuwave\Lighthouse\Support\Http\Middleware\AcceptJson::class,
            \Code7day\LighthousePassportLogin\Http\Middleware\AuthenticateWithApiGuard::class,
        ]);
    }
}
