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
            $table->string('status')->default('Ngambang')->after('location_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('civils', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

//     ALTER TABLE civils 
// ADD COLUMN status VARCHAR(255) DEFAULT 'active' AFTER location_type;
};
