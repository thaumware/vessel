<?php

declare(strict_types=1);

namespace App\Shared\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AutoUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:update {--branch= : Git branch to track (default: APP_UPDATE_BRANCH or main)} {--no-migrate : Skip database migrations}';

    /**
     * The console command description.
     */
    protected $description = 'Auto-update the application from Git (dev-only), run composer install, and migrations.';

    public function handle(): int
    {
        if (!env('APP_ALLOW_UPDATE', false)) {
            $this->error('Auto-update is disabled. Set APP_ALLOW_UPDATE=true to enable.');
            return self::FAILURE;
        }

        $branch = $this->option('branch') ?: env('APP_UPDATE_BRANCH', 'main');
        $skipMigrate = (bool) $this->option('no-migrate');
        $cwd = base_path();

        if (!is_dir($cwd.'/.git')) {
            $this->error('Git repository not found at base_path().');
            return self::FAILURE;
        }

        $this->info("Checking updates from origin/{$branch}...");

        if (!$this->runProcess(['git', 'fetch', 'origin', $branch], $cwd, 'Fetch failed')) {
            return self::FAILURE;
        }

        $local = $this->readProcess(['git', 'rev-parse', 'HEAD'], $cwd);
        $remote = $this->readProcess(['git', 'rev-parse', "origin/{$branch}"], $cwd);

        if (!$local || !$remote) {
            $this->error('Unable to read git revisions.');
            return self::FAILURE;
        }

        if (trim($local) === trim($remote)) {
            $this->info('Already up to date.');
            return self::SUCCESS;
        }

        $this->info('Pulling latest changes...');
        if (!$this->runProcess(['git', 'pull', '--ff-only', 'origin', $branch], $cwd, 'Git pull failed')) {
            return self::FAILURE;
        }

        $this->info('Installing PHP dependencies...');
        if (!$this->runProcess(['composer', 'install', '--no-interaction', '--prefer-dist', '--no-progress'], $cwd, 'Composer install failed')) {
            return self::FAILURE;
        }

        if (!$skipMigrate) {
            $this->info('Running migrations...');
            if (!$this->runProcess(['php', 'artisan', 'migrate', '--force'], $cwd, 'Migrations failed')) {
                return self::FAILURE;
            }
        } else {
            $this->info('Skipping migrations (per option).');
        }

        $this->info('Clearing caches...');
        $this->runProcess(['php', 'artisan', 'config:clear'], $cwd);
        $this->runProcess(['php', 'artisan', 'cache:clear'], $cwd);
        $this->runProcess(['php', 'artisan', 'route:clear'], $cwd);
        $this->runProcess(['php', 'artisan', 'view:clear'], $cwd);

        $this->info('Update completed.');
        return self::SUCCESS;
    }

    private function runProcess(array $command, string $cwd, string $errorMessage = null): bool
    {
        $process = new Process($command, $cwd, null, null, 600);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error($errorMessage ?? 'Command failed');
            return false;
        }

        return true;
    }

    private function readProcess(array $command, string $cwd): ?string
    {
        $process = new Process($command, $cwd, null, null, 60);
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : null;
    }
}
