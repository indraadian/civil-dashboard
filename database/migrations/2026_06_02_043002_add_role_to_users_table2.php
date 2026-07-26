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
        // Duplicate migration kept for history. The role column is created by an earlier migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: nothing to rollback here.
    }
};
