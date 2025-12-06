<?php

namespace App\Shared\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CloneTestDatabaseCommand extends Command
{
    protected $signature = 'vessel:test:clone {--fresh : Run migrate:fresh on target before cloning} {--source= : Source connection name} {--target=testing : Target connection name}';

    protected $description = 'Clone the default database into the testing database to keep dev/test in sync';

    public function handle(): int
    {
        $source = $this->option('source') ?: config('database.default');
        $target = $this->option('target') ?: 'testing';
        $fresh = (bool) $this->option('fresh');

        $this->info("Cloning database from [{$source}] to [{$target}]...");

        if ($fresh) {
            $this->comment('Running migrate:fresh on target...');
            Artisan::call('migrate:fresh', ['--database' => $target, '--force' => true]);
            $this->output->write(Artisan::output());
        }

        $sourceConn = DB::connection($source);
        $targetConn = DB::connection($target);

        $driver = $targetConn->getDriverName();
        $this->disableForeignKeys($targetConn, $driver);

        try {
            $tables = $sourceConn->getDoctrineSchemaManager()->listTableNames();
        } catch (\Throwable $e) {
            $this->error('Cannot list tables from source: ' . $e->getMessage());
            return self::FAILURE;
        }

        foreach ($tables as $table) {
            if ($table === 'migrations') {
                // Skip migrations table to avoid confusing the repository
                continue;
            }

            if (!Schema::connection($target)->hasTable($table)) {
                $this->warn("Skipping [{$table}] (not present in target)");
                continue;
            }

            $this->comment("Copying [{$table}]...");

            try {
                // Truncate or delete existing rows
                try {
                    $targetConn->table($table)->truncate();
                } catch (\Throwable $e) {
                    $targetConn->table($table)->delete();
                }

                $sourceConn->table($table)
                    ->orderByRaw('1')
                    ->chunk(500, function ($rows) use ($targetConn, $table) {
                        $payload = $rows->map(fn ($row) => (array) $row)->toArray();
                        if (!empty($payload)) {
                            $targetConn->table($table)->insert($payload);
                        }
                    });
            } catch (\Throwable $e) {
                $this->error("Failed copying [{$table}]: " . $e->getMessage());
            }
        }

        $this->enableForeignKeys($targetConn, $driver);

        $this->info('Clone completed.');
        return self::SUCCESS;
    }

    private function disableForeignKeys($connection, string $driver): void
    {
        try {
            if (in_array($driver, ['mysql', 'mariadb'])) {
                $connection->statement('SET FOREIGN_KEY_CHECKS=0');
            } elseif ($driver === 'sqlite') {
                $connection->statement('PRAGMA foreign_keys = OFF');
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function enableForeignKeys($connection, string $driver): void
    {
        try {
            if (in_array($driver, ['mysql', 'mariadb'])) {
                $connection->statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'sqlite') {
                $connection->statement('PRAGMA foreign_keys = ON');
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
