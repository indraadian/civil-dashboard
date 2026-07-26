<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

it('does not fail when adding status migration runs twice', function () {
    Schema::dropIfExists('civils');

    Schema::create('civils', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });

    $migration = include database_path('migrations/2026_06_06_111755_add_status_to_civils_table.php');

    $migration->up();
    $migration->up();

    expect(Schema::hasColumn('civils', 'status'))->toBeTrue();
});
