<?php

namespace Thaumware\Portal\Core;

use Thaumware\Portal\Contracts\StorageAdapter;

/**
 * Manages portal origins and relations
 */
class PortalRegistry
{
    public function __construct(
        private StorageAdapter $adapter
    ) {}

    public function register(string $name, string $source, string $type): string
    {
        // Check if exists
        $existing = $this->adapter->findOriginByName($name);
        if ($existing) {
            return $existing['id'];
        }

        return $this->adapter->createOrigin($name, $source, $type);
    }

    public function link(
        string $modelId,
        string $modelType,
        string $originName,
        string $relatedId,
        ?array $metadata
    ): void {
        $origin = $this->adapter->findOriginByName($originName);
        
        if (!$origin) {
            throw new \Exception("Portal origin '$originName' not registered");
        }

        $this->adapter->createPortal(
            $modelId,
            $modelType,
            $origin['id'],
            $relatedId,
            $metadata
        );
    }

    public function deactivate(string $originName): void
    {
        $this->adapter->deactivateOrigin($originName);
    }
}
