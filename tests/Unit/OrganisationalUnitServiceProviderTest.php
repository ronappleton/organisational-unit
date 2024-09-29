<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;

class OrganisationalUnitServiceProviderTest extends TestCase
{
    public function testBootLoadsMigrations(): void
    {
        // Arrange: Mock the migration path
        $migrationsPath = realpath(__DIR__.'/../../database/migrations');

        // Act: Check if the migrations path exists
        $this->assertTrue(File::exists($migrationsPath));

        // Assert: Check if the service provider loads the migrations
        $this->artisan('migrate', ['--path' => $migrationsPath])
            ->assertExitCode(0);
    }
}
