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
        Schema::create('schedule_recurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->json('byweekday')->nullable();
            $table->integer('bysetpos')->nullable();
            $table->integer('interval')->default(1);
            $table->timestamp('until')->nullable();
            $table->timestamp('start_after')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_recurrences');
    }
};
