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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'rt')) {
                $table->dropColumn('rt');
            }
            if (Schema::hasColumn('users', 'rw')) {
                $table->dropColumn('rw');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rw')) {
                $table->string('rw', 5)->nullable();
            }
            if (!Schema::hasColumn('users', 'rt')) {
                $table->string('rt', 5)->nullable();
            }
        });
    }
};
