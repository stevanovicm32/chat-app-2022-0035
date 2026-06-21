<?php

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\DataAccess\Repositories\Contracts\UlogaRepositoryInterface;
use App\DataAccess\Repositories\UlogaRepository;
use App\DataAccess\Repositories\Contracts\KorisnikRepositoryInterface;
use App\DataAccess\Repositories\KorisnikRepository;
use App\DataAccess\Repositories\Contracts\ChatRepositoryInterface;
use App\DataAccess\Repositories\ChatRepository;
use App\DataAccess\Repositories\Contracts\PorukaRepositoryInterface;
use App\DataAccess\Repositories\PorukaRepository;
use App\DataAccess\Repositories\Contracts\DatotekaRepositoryInterface;
use App\DataAccess\Repositories\DatotekaRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UlogaRepositoryInterface::class, UlogaRepository::class);
        $this->app->bind(KorisnikRepositoryInterface::class, KorisnikRepository::class);
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
        $this->app->bind(PorukaRepositoryInterface::class, PorukaRepository::class);
        $this->app->bind(DatotekaRepositoryInterface::class, DatotekaRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

