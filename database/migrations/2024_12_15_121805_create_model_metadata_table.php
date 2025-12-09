<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_metadata', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->morphs('metadatable');

            $table->string('key');

            $table->string('string_value')->nullable();
            $table->bigInteger('int_value')->nullable();
            $table->decimal('decimal_value', 15, 4)->nullable();
            $table->boolean('bool_value')->nullable();
            $table->json('json_value')->nullable();

            $table->timestamps();

            $table->index(['metadatable_type', 'key']);
            $table->index(['metadatable_type', 'metadatable_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_metadata');
    }
};
