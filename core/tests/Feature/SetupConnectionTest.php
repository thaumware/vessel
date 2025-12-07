<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SetupConnectionTest extends TestCase
{
    public function test_mysql_connection_with_runtime_config(): void
    {
        // Simular exactamente lo que hace SetupController
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.host', '127.0.0.1');
        Config::set('database.connections.mysql.port', 3307);
        Config::set('database.connections.mysql.database', 'vessel_db');
        Config::set('database.connections.mysql.username', 'root');
        Config::set('database.connections.mysql.password', '');
        Config::set('database.connections.mysql.unix_socket', '');

        DB::purge('mysql');

        // Verificar config
        $config = Config::get('database.connections.mysql');
        $this->assertEquals(3307, $config['port']);
        $this->assertEquals('127.0.0.1', $config['host']);

        // Intentar conexión
        try {
            $pdo = DB::connection('mysql')->getPdo();
            $this->assertInstanceOf(\PDO::class, $pdo);
            echo "\n✓ Conexión exitosa con puerto " . $config['port'] . "\n";
        } catch (\Exception $e) {
            $this->fail("No se pudo conectar: " . $e->getMessage());
        }
    }

    public function test_setup_endpoint_with_mysql(): void
    {
        $response = $this->postJson('/setup', [
            'db_driver' => 'mysql',
            'db_host' => '127.0.0.1',
            'db_port' => '3307',
            'db_name' => 'vessel_db',
            'db_user' => 'root',
            'db_pass' => '',
            'app_url' => 'http://localhost',
            'admin_user' => 'admin',
            'admin_pass' => 'test123',
            'fresh' => false,
        ]);

        echo "\n";
        echo "Status: " . $response->status() . "\n";
        echo "Response: " . $response->content() . "\n";

        if ($response->status() !== 200) {
            $this->fail("Setup falló: " . $response->json('error'));
        }

        $this->assertTrue($response->json('success'));
    }
}
