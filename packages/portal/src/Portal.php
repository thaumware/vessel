<?php

namespace Thaumware\Portal;

use Thaumware\Portal\Core\PortalRegistry;
use Thaumware\Portal\Core\RelationLoader;

/**
 * Portal - Cross-service data relationships
 * 
 * Usage:
 *   Portal::register('items', 'http://catalog:9110/api/items')
 *   Portal::attach($models)
 */
class Portal
{
    private static ?PortalRegistry $registry = null;
    private static ?RelationLoader $loader = null;

    private static function registry(): PortalRegistry
    {
        return self::$registry ??= app(PortalRegistry::class);
    }

    private static function loader(): RelationLoader
    {
        return self::$loader ??= app(RelationLoader::class);
    }

    /**
     * Register origin
     * 
     * @param string $name Unique name
     * @param string $source Table name or URL
     * @param string $type 'table' or 'http'
     */
    public static function register(string $name, string $source, string $type = 'table'): string
    {
        return self::registry()->register($name, $source, $type);
    }

    /**
     * Create relation
     * 
     * @param string $modelId Local model ID
     * @param string $modelType Model class name
     * @param string $originName Origin name
     * @param string $relatedId Related entity ID
     */
    public static function link(
        string $modelId,
        string $modelType,
        string $originName,
        string $relatedId,
        ?array $metadata = null
    ): void {
        self::registry()->link($modelId, $modelType, $originName, $relatedId, $metadata);
    }

    /**
     * Load relations into models (batch)
     * 
     * @param array|object $models Collection or array
     * @return array|object Same type as input
     */
    public static function attach($models)
    {
        return self::loader()->attach($models);
    }

    /**
     * Deactivate origin
     */
    public static function deactivate(string $originName): void
    {
        self::registry()->deactivate($originName);
    }
}
