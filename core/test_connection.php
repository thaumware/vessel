<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

Config::set('database.connections.mysql.host', '127.0.0.1');
Config::set('database.connections.mysql.port', 3307);
Config::set('database.connections.mysql.database', 'vessel_db');
Config::set('database.connections.mysql.username', 'root');
Config::set('database.connections.mysql.password', '');
Config::set('database.connections.mysql.unix_socket', '');

DB::purge('mysql');

$config = Config::get('database.connections.mysql');
echo "Config port: " . $config['port'] . PHP_EOL;
echo "Config unix_socket: " . ($config['unix_socket'] ?: 'empty') . PHP_EOL;

try {
    $pdo = DB::connection('mysql')->getPdo();
    echo "✓ Conexión Laravel exitosa" . PHP_EOL;
} catch (Exception $e) {
    echo "✗ Error Laravel: " . $e->getMessage() . PHP_EOL;
    
    // Intentar conexión directa
    try {
        $dsn = "mysql:host=127.0.0.1;port=3307;dbname=vessel_db";
        $pdo = new PDO($dsn, 'root', '');
        echo "✓ Conexión PDO directa exitosa" . PHP_EOL;
    } catch (Exception $e2) {
        echo "✗ Error PDO directo: " . $e2->getMessage() . PHP_EOL;
    }
}
