<?php

namespace App\Shared\Application;

use Illuminate\Support\Facades\Process;

class RunTests
{
    public function execute(array $options = []): array
    {
        $filter = $options['filter'] ?? '';
        
        // Parse legacy filter format (--filter Unit) 
        if (strpos($filter, '--filter ') === 0) {
            $filter = str_replace('--filter ', '', $filter);
        }
        
        $command = 'php artisan test';
        
        if ($filter) {
            $command .= " --filter={$filter}";
        }

        // Agregar flags adicionales
        $command .= ' --stop-on-failure';

        $startTime = microtime(true);
        
        $result = Process::timeout(300)
            ->path(base_path())
            ->run($command);

        $duration = microtime(true) - $startTime;

        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
            'exit_code' => $result->exitCode(),
            'duration' => round($duration, 2),
            'command' => $command,
        ];
    }

    public function listTestSuites(): array
    {
        $phpunitXml = base_path('phpunit.xml');
        
        if (!file_exists($phpunitXml)) {
            return [];
        }

        $xml = simplexml_load_file($phpunitXml);
        $suites = [];

        foreach ($xml->testsuites->testsuite as $suite) {
            $name = (string) $suite['name'];
            $directory = (string) $suite->directory;
            
            $suites[] = [
                'name' => $name,
                'directory' => $directory,
            ];
        }

        return $suites;
    }

    public function getTestFiles(string $directory): array
    {
        $path = base_path($directory);
        
        if (!is_dir($path)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), 'Test.php')) {
                $files[] = [
                    'name' => $file->getFilename(),
                    'path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname()),
                ];
            }
        }

        return $files;
    }
}
