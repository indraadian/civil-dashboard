<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

it('does not fail when adding date_of_birth migration runs twice', function () {
    Schema::dropIfExists('civils');

    Schema::create('civils', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });

    $migration = include database_path('migrations/2026_05_31_165049_add_date_of_birth_to_civils_table.php');

    $migration->up();
    $migration->up();

    expect(Schema::hasColumn('civils', 'date_of_birth'))->toBeTrue();
});
