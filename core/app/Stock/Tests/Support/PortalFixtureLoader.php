<?php

namespace App\Stock\Tests\Support;

use RuntimeException;

class PortalFixtureLoader
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    public function load(string $fixtureName): PortalStub
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . $fixtureName;
        if (!file_exists($path)) {
            throw new RuntimeException("Portal fixture missing: {$path}");
        }
        return new PortalStub($path);
    }
}
