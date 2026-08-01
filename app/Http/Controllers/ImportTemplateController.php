<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportTemplateController extends Controller
{
    /**
     * Download import template dynamically based on module configuration.
     */
    public function download(string $module): BinaryFileResponse
    {
        $config = config("import_templates.{$module}");

        if (!$config) {
            abort(404, "Template import untuk modul '{$module}' tidak ditemukan.");
        }

        $filePath = public_path('templates/' . $config['file']);

        if (!file_exists($filePath)) {
            abort(404, "File template {$config['file']} tidak ditemukan.");
        }

        return response()->download($filePath, $config['filename'], [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
