<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chart_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable()->default('Default');
            $table->text('description')->nullable();
            $table->jsonb('data')->default('[]');
            $table->jsonb('layout')->default('{}');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_templates');
    }
};
