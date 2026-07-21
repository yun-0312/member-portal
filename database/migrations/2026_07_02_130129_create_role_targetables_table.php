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
        Schema::create('role_targetables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('targetable_id');
            $table->string('targetable_type');
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['targetable_id', 'targetable_type', 'role_id'],'role_targetables_unique');

            $table->index(['targetable_id', 'targetable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_targetable');
    }
};
