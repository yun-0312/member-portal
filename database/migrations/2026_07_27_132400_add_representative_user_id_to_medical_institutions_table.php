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
        Schema::table('medical_institutions', function (Blueprint $table) {
            $table->foreignId('representative_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_institutions', function (Blueprint $table) {
            $table->dropForeign(['representative_user_id']);
            $table->dropColumn('representative_user_id');
        });
    }
};
