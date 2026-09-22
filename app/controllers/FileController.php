<?php

namespace App\Controllers;

use App\Core\Controller;

class FileController extends Controller
{
    public function avatar(string $filename): void
    {
        $this->serveFile('avatars', $filename);
    }

    private function serveFile(string $type, string $filename): void
    {
        // Sanitize — no path traversal
        $filename = basename($filename);
        $path     = ROOT_PATH . '/uploads/' . $type . '/' . $filename;

        if (!file_exists($path)) $this->abort(404, 'File not found');

        $mime = mime_content_type($path);
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowed, true)) $this->abort(403, 'Forbidden');

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=86400');
        readfile($path);
        exit;
    }
}
