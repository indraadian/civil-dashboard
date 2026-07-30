<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('civils', function (Blueprint $table) {
            if (!Schema::hasColumn('civils', 'gender')) {
                $table->string('gender', 10)->nullable()->after('date_of_birth');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('civils', function (Blueprint $table) {
            if (Schema::hasColumn('civils', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
};
