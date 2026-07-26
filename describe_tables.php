<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');
foreach (['users', 'civils'] as $table) {
    echo "TABLE: {$table}\n";
    $rows = $db->select("DESCRIBE {$table}");
    foreach ($rows as $col) {
        echo sprintf("%s %s %s %s %s %s\n", 
            $col->Field,
            $col->Type,
            $col->Null === 'NO' ? 'NOT NULL' : 'NULL',
            $col->Key ? 'KEY' : '',
            $col->Extra ?: '',
            $col->Default === null ? 'DEFAULT NULL' : 'DEFAULT ' . $col->Default
        );
    }
    echo "\n";
}
