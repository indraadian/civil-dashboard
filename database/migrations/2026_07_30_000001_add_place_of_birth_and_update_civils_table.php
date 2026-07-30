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
        Schema::table('civils', function (Blueprint $table) {
            if (!Schema::hasColumn('civils', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable()->after('name');
            }
            $table->string('location_type')->nullable()->change();
            $table->string('status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('civils', function (Blueprint $table) {
            if (Schema::hasColumn('civils', 'place_of_birth')) {
                $table->dropColumn('place_of_birth');
            }
        });
    }
};
