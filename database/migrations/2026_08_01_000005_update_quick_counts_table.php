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
        Schema::table('quick_counts', function (Blueprint $table) {
            $table->string('officer_name')->nullable()->after('tps_id');
            $table->string('officer_phone')->nullable()->after('officer_name');
            $table->timestamp('input_at')->nullable()->after('officer_phone');
            $table->integer('invalid_votes')->default(0)->after('c1_photo');

            if (Schema::hasColumn('quick_counts', 'vote_count')) {
                $table->dropColumn('vote_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quick_counts', function (Blueprint $table) {
            $table->integer('vote_count')->default(0);
            $table->dropColumn(['officer_name', 'officer_phone', 'input_at', 'invalid_votes']);
        });
    }
};
