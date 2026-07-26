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
        Schema::create('content_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('content_categories')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('content_subcategories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('display_type', ['list', 'children', 'year_archive'])->default('list');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_subcategories');
    }
};
