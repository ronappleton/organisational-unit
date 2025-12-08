<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organisational_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('organisational_units')
                ->nullOnDelete();
            $table->nullableMorphs('entity');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->index('tenant_id');

            $table->softDeletes();
            $table->timestamps();

            $table->index('parent_id');
            $table->index('type');
            $table->index('code');
            $table->index(['tenant_id', 'type']);

            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisational_units');
    }
};
