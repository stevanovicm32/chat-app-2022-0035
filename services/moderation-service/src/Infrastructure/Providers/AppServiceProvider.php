<?php

namespace App\Infrastructure\Providers;

use App\Infrastructure\Clients\IdentityClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IdentityClient::class, function () {
            return new IdentityClient();
        });
    }

    public function boot(): void
    {
        //
    }
}
