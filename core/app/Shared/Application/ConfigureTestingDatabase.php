<?php

namespace App\Shared\Application;

class ConfigureTestingDatabase
{
    public function execute(array $config): array
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            throw new \RuntimeException('.env file not found');
        }

        $envContent = file_get_contents($envPath);
        
        // Variables a actualizar
        $variables = [
            'DB_TEST_CONNECTION' => $config['connection'] ?? 'mysql',
            'DB_TEST_HOST' => $config['host'] ?? '127.0.0.1',
            'DB_TEST_PORT' => $config['port'] ?? '3306',
            'DB_TEST_DATABASE' => $config['database'] ?? 'vessel_db_test',
            'DB_TEST_USERNAME' => $config['username'] ?? 'root',
            'DB_TEST_PASSWORD' => $config['password'] ?? '',
        ];

        foreach ($variables as $key => $value) {
            // Si la variable existe, reemplazarla
            if (preg_match("/^{$key}=.*$/m", $envContent)) {
                $envContent = preg_replace(
                    "/^{$key}=.*$/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Si no existe, agregarla al final
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);

        // Limpiar cache de config
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        // Recargar config
        app('config')->set('database.connections.testing', [
            'driver' => $config['connection'] ?? 'mysql',
            'host' => $config['host'] ?? '127.0.0.1',
            'port' => $config['port'] ?? '3306',
            'database' => $config['database'] ?? 'vessel_db_test',
            'username' => $config['username'] ?? 'root',
            'password' => $config['password'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        return [
            'success' => true,
            'message' => 'Testing database configured successfully',
            'config' => $variables,
        ];
    }

    public function getCurrentConfig(): array
    {
        return [
            'connection' => env('DB_TEST_CONNECTION', 'mysql'),
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '3306'),
            'database' => env('DB_TEST_DATABASE', 'vessel_db_test'),
            'username' => env('DB_TEST_USERNAME', 'root'),
            'password' => env('DB_TEST_PASSWORD', ''),
        ];
    }

    public function testConnection(array $config): array
    {
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s',
                $config['connection'] ?? 'mysql',
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? '3306',
                $config['database'] ?? 'vessel_db_test'
            );

            $pdo = new \PDO(
                $dsn,
                $config['username'] ?? 'root',
                $config['password'] ?? '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            return [
                'success' => true,
                'message' => 'Connection successful',
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
