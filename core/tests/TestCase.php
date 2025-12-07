<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Helper to make HTTP requests with module-specific adapter header.
     */
    protected function withAdapter(string $module, string $adapter = 'local'): static
    {
        return $this->withHeader('X-' . strtoupper($module) . '-ADAPTER', $adapter);
    }

    /**
     * Helper to set workspace context.
     */
    protected function forWorkspace(string $workspaceId): static
    {
        return $this->withHeader('X-WORKSPACE-ID', $workspaceId);
    }
}
