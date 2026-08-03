<?php
/**
 * Script Bantuan Pembuat Storage Link Laravel (Tanpa SSH / Terminal)
 * Akses file ini dari browser: https://indevbisnis.net/link-storage.php
 */

// Tentukan lokasi target (storage/app/public) dan link (public/storage)
$target = __DIR__ . '/../storage/app/public';
$shortcut = __DIR__ . '/storage';

// Jika struktur cPanel di mana document root adalah public_html
if (!file_exists($target)) {
    $target = dirname(__DIR__) . '/storage/app/public';
}

echo "<h2>Laravel Storage Link Helper</h2>";
echo "<p>Target: <code>{$target}</code></p>";
echo "<p>Shortcut: <code>{$shortcut}</code></p>";

if (!file_exists($target)) {
    @mkdir($target, 0755, true);
    echo "<p>Membuat direktori target <code>storage/app/public</code>...</p>";
}

if (file_exists($shortcut)) {
    echo "<p style='color: blue; font-weight: bold;'>Status: Folder / Symlink 'public/storage' sudah ada di server!</p>";
} else {
    if (@symlink($target, $shortcut)) {
        echo "<p style='color: green; font-weight: bold;'>BERHASIL: Symbolic link berhasil dibuat!</p>";
        echo "<p>Foto sekarang sudah bisa diakses via browser.</p>";
    } else {
        // Coba pemanggilan Artisan via PHP jika symlink native dilarang
        try {
            require __DIR__ . '/../vendor/autoload.php';
            $app = require_once __DIR__ . '/../bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
            $response = $kernel->handle(
                $request = Illuminate\Http\Request::capture()
            );
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            echo "<p style='color: green; font-weight: bold;'>BERHASIL via Artisan: " . \Illuminate\Support\Facades\Artisan::output() . "</p>";
        } catch (\Throwable $e) {
            echo "<p style='color: red; font-weight: bold;'>GAGAL: " . $e->getMessage() . "</p>";
            echo "<p>Silakan buat symlink manual via cPanel File Manager atau Cron Job.</p>";
        }
    }
}
