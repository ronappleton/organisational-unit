<?php

declare(strict_types=1);

namespace Tests;

use Appleton\OrganisationalUnit\OrganisationalUnitServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            OrganisationalUnitServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $migration = new class extends Migration
        {
            public function up(): void
            {
                Schema::create('some_types', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->timestamps();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('some_types');
            }
        };

        $migration->up();
    }
}
