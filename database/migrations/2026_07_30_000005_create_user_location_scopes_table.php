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
        if (!Schema::hasTable('user_location_scopes')) {
            Schema::create('user_location_scopes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('rw_id')->constrained('rws')->onDelete('cascade');
                $table->foreignId('rt_id')->nullable()->constrained('rts')->onDelete('cascade');
                $table->timestamps();

                $table->index(['user_id', 'rw_id', 'rt_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_location_scopes');
    }
};
