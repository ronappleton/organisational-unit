<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit;

use Illuminate\Support\ServiceProvider;

class OrganisationalUnitServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
