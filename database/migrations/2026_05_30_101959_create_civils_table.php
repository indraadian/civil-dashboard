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
        Schema::create('civils', function (Blueprint $table) {
            $table->id(); // ID otomatis (Primary Key)
            $table->string('nik', 16)->unique(); // NIK biasanya 16 digit dan tidak boleh kembar
            $table->string('name');
            $table->string('hamlet')->nullable(); // Dusun (nullable artinya boleh kosong)
            $table->string('rw', 3); // RW (misal: 001)
            $table->string('rt', 3); // RT (misal: 005)
            $table->text('address'); // Pake text karena alamat bisa panjang
            $table->enum('location_type', ['village', 'housing']); // Pilihan: Kampung (village) atau Perumahan (housing)
            $table->timestamps(); // Otomatis membuat kolom created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('civils');
    }
};
