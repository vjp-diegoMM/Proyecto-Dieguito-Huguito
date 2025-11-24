<?php
require_once 'vendor/autoload.php';

use Dwes\ProyectoVideoclub\Videoclub;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

echo "Testing Autoloading...\n";

try {
    $vc = new Videoclub("Test");
    echo "Videoclub class loaded successfully.\n";
} catch (\Throwable $e) {
    echo "Error loading Videoclub: " . $e->getMessage() . "\n";
}

try {
    $log = new Logger('name');
    $log->pushHandler(new StreamHandler('app.log', Logger::WARNING));
    $log->warning('Foo');
    echo "Monolog loaded successfully.\n";
} catch (\Throwable $e) {
    echo "Error loading Monolog: " . $e->getMessage() . "\n";
}
