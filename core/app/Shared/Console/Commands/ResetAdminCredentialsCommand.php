<?php

namespace App\Shared\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Infrastructure\ConfigStore;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ResetAdminCredentialsCommand extends Command
{
    protected $signature = 'admin:reset {--user= : Nuevo usuario admin} {--password= : Nuevo password admin}';

    protected $description = 'Reset admin basic-auth credentials stored in shared_config';

    public function handle(): int
    {
        $user = $this->option('user') ?: 'admin';
        $pass = $this->option('password') ?: 'admin123';

        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);

        try {
            $this->ensureConfigTable();
            $store->set('admin.root', $user);
            $store->set('admin.root_password', $pass);
            $store->set('app.installed', true);
        } catch (\Throwable $e) {
            $this->error('No se pudo escribir en shared_config: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->writeEnv([
            'ADMIN_ROOT' => $user,
            'ADMIN_ROOT_PASSWORD' => $pass,
            'APP_INSTALLED' => 'true',
        ]);

        $this->info("Credenciales actualizadas. Usuario: {$user}");
        $this->line('Password: ' . $pass);
        $this->line('Usa Basic Auth en /admin con estos datos.');

        return self::SUCCESS;
    }

    private function ensureConfigTable(): void
    {
        if (Schema::hasTable('shared_config')) {
            return;
        }

        Schema::create('shared_config', function ($table) {
            $table->string('key')->primary();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    private function writeEnv(array $pairs): void
    {
        $envPath = base_path('.env');
        $contents = file_exists($envPath) ? file_get_contents($envPath) : '';

        foreach ($pairs as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = $key . '=' . $this->escapeEnvValue($value);

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $replacement, $contents);
            } else {
                $contents = rtrim($contents, "\r\n") . PHP_EOL . $replacement . PHP_EOL;
            }
        }

        file_put_contents($envPath, $contents);
    }

    private function escapeEnvValue($value): string
    {
        if ($value === null) {
            return '';
        }

        $needsQuotes = str_contains((string) $value, ' ');
        $escaped = str_replace(["\n", '"'], ['\\n', '\\"'], (string) $value);

        return $needsQuotes ? '"' . $escaped . '"' : $escaped;
    }
}
