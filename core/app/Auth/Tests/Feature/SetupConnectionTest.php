<?php

namespace App\Auth\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SetupConnectionTest extends TestCase
{
    public function test_mysql_connection_with_runtime_config(): void
    {
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.host', '127.0.0.1');
        Config::set('database.connections.mysql.port', 3307);
        Config::set('database.connections.mysql.database', 'vessel');
        Config::set('database.connections.mysql.username', 'root');
        Config::set('database.connections.mysql.password', '');
        Config::set('database.connections.mysql.unix_socket', '');

        DB::purge('mysql');

        $config = Config::get('database.connections.mysql');
        $this->assertEquals(3307, $config['port']);
        $this->assertEquals('127.0.0.1', $config['host']);

        try {
            $pdo = DB::connection('mysql')->getPdo();
            $this->assertInstanceOf(\PDO::class, $pdo);
        } catch (\Exception $e) {
            $this->fail('Connection failed: ' . $e->getMessage());
        }
    }

    public function test_setup_endpoint_with_mysql(): void
    {
        $response = $this->postJson('/setup', [
            'db_driver' => 'mysql',
            'db_host' => '127.0.0.1',
            'db_port' => '3307',
            'db_name' => 'vessel',
            'db_user' => 'root',
            'db_pass' => '',
            'app_url' => 'http://localhost',
            'admin_user' => 'admin',
            'admin_pass' => 'test123',
            'fresh' => false,
        ]);

        if ($response->status() !== 200) {
            $this->fail('Setup failed: ' . $response->json('error'));
        }

        $this->assertTrue($response->json('success'));
    }
}