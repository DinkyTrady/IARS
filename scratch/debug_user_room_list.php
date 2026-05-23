<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function listDir($path)
{
    if (! is_dir($path)) {
        return;
    }
    $files = glob($path.'/*');
    foreach ($files as $file) {
        if (is_dir($file)) {
            listDir($file);
        } else {
            echo '=== VOLT COMPILED FILE: '.str_replace(storage_path(), '', $file)." ===\n";
            echo file_get_contents($file)."\n";
            echo "========================================================\n\n";
        }
    }
}

try {
    listDir(storage_path('framework/views/livewire'));
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
