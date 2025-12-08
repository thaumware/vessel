<?php

namespace App\Shared\Tests\Traits;

use App\Shared\Infrastructure\ModuleRegistry;

/**
 * Trait para tests que necesitan verificar si módulos están activos
 * antes de intentar cargar relaciones cross-module.
 */
trait ModuleAwareTesting
{
    private static ?ModuleRegistry $moduleRegistry = null;

    /**
     * Verifica si un módulo está activo.
     */
    protected function isModuleEnabled(string $module): bool
    {
        if (self::$moduleRegistry === null) {
            // Intentar obtener del container si existe (tests de integración)
            if (function_exists('app') && app()->bound(ModuleRegistry::class)) {
                self::$moduleRegistry = app(ModuleRegistry::class);
            } else {
                // Tests unitarios: asumir todos activos por defecto
                return true;
            }
        }

        return self::$moduleRegistry->enabled($module);
    }

    /**
     * Skip test si módulo no está activo.
     */
    protected function requireModule(string $module, string $reason = null): void
    {
        if (!$this->isModuleEnabled($module)) {
            $message = $reason ?? "Module '{$module}' is not enabled";
            $this->markTestSkipped($message);
        }
    }

    /**
     * Skip test si ALGUNO de los módulos no está activo.
     */
    protected function requireModules(array $modules, string $reason = null): void
    {
        $missing = [];
        foreach ($modules as $module) {
            if (!$this->isModuleEnabled($module)) {
                $missing[] = $module;
            }
        }

        if (!empty($missing)) {
            $message = $reason ?? sprintf(
                "Required modules not enabled: %s",
                implode(', ', $missing)
            );
            $this->markTestSkipped($message);
        }
    }

    /**
     * Ejecuta código solo si módulo está activo.
     */
    protected function whenModuleEnabled(string $module, callable $callback): mixed
    {
        if ($this->isModuleEnabled($module)) {
            return $callback();
        }
        return null;
    }

    /**
     * Mock de CatalogGateway que retorna datos vacíos si Catalog no está activo.
     */
    protected function createCatalogGatewayMock(): object
    {
        $mock = $this->createMock(\App\Stock\Domain\Interfaces\CatalogGatewayInterface::class);
        
        if ($this->isModuleEnabled('catalog')) {
            // Si está activo, configurar comportamiento normal
            $mock->method('attachCatalogData')
                ->willReturnCallback(function ($items) {
                    // Simular enriquecimiento básico
                    return array_map(function ($item) {
                        return [
                            'item_id' => $item->getItemId(),
                            'catalog_item' => [
                                'name' => 'Test Item ' . substr($item->getItemId(), 0, 8),
                                'uom_id' => 'test-uom-id',
                                'uom_symbol' => 'unit',
                            ],
                        ];
                    }, is_array($items) ? $items : iterator_to_array($items));
                });
        } else {
            // Si no está activo, retornar sin enriquecimiento
            $mock->method('attachCatalogData')
                ->willReturnCallback(function ($items) {
                    return array_map(function ($item) {
                        return ['item_id' => $item->getItemId()];
                    }, is_array($items) ? $items : iterator_to_array($items));
                });
        }

        $mock->method('catalogItemExists')->willReturn(true);
        $mock->method('getDefaultOriginName')->willReturn('internal_catalog');

        return $mock;
    }

    /**
     * Mock de LocationGateway con comportamiento básico.
     */
    protected function createLocationGatewayMock(array $descendants = []): object
    {
        $mock = $this->createMock(\App\Stock\Domain\Interfaces\LocationGatewayInterface::class);
        
        $mock->method('getDescendantIds')
            ->willReturn($descendants);
        
        $mock->method('getChildrenIds')
            ->willReturn($descendants);
        
        $mock->method('exists')
            ->willReturn(true);

        return $mock;
    }
}
