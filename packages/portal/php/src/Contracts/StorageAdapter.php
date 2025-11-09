<?php

namespace Thaumware\Portal\Contracts;

/**
 * Storage adapter interface
 */
interface StorageAdapter
{
    public function findOriginByName(string $name): ?array;
    
    public function findOriginById(string $id): ?array;
    
    public function createOrigin(string $name, string $source, string $type): string;
    
    public function createPortal(
        string $modelId,
        string $modelType,
        string $originId,
        string $relatedId,
        ?array $metadata
    ): void;
    
    public function findPortalsByModelIds(array $modelIds): array;
    
    public function deactivateOrigin(string $name): void;
}
