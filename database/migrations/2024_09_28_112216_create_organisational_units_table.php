<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisational_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->references('id')->on('organisational_units');
            $table->uuidMorphs('entity');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['parent_id', 'entity_id', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisational_units');
    }
};
