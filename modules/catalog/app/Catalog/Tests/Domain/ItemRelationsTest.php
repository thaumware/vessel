<?php

namespace App\Catalog\Tests\Domain;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Entities\ItemRelation;
use App\Catalog\Domain\Entities\ItemSpecification;
use App\Catalog\Domain\Entities\ItemIdentifier;
use App\Catalog\Domain\ValueObjects\ItemStatus;
use App\Catalog\Domain\ValueObjects\IdentifierType;
use App\Catalog\Domain\ValueObjects\RelationType;
use App\Catalog\Domain\ValueObjects\SpecDataType;
use App\Catalog\Tests\CatalogTestCase;

/**
 * Test: Relaciones entre Items (Repuestos, Maquinaria, Compatibilidad)
 * 
 * Casos:
 * - Repuestos que componen maquinaria (BOM - Bill of Materials)
 * - Repuestos compatibles con múltiples máquinas
 * - Accesorios opcionales
 * - Productos que requieren otros
 * - Kits/bundles
 */
class ItemRelationsTest extends CatalogTestCase
{
    private string $workspaceId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workspaceId = $this->generateUuid();
    }

    public function test_machine_with_components_bom(): void
    {
        // Máquina principal
        $machineId = $this->generateUuid();
        $machine = new Item(
            id: $machineId,
            name: 'Compresor Industrial 500L',
            status: ItemStatus::Active,
            description: 'Compresor de aire industrial 500 litros',
            workspaceId: $this->workspaceId,
        );

        // Componentes/repuestos
        $motorId = $this->generateUuid();
        $filterId = $this->generateUuid();
        $valveId = $this->generateUuid();
        $beltId = $this->generateUuid();

        // BOM - Bill of Materials (componentes de la máquina)
        $components = [
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $machineId,
                related_item_id: $motorId,
                relation_type: RelationType::Component,
                quantity: 1,
                is_required: true,
                meta: ['position' => 'main_unit'],
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $machineId,
                related_item_id: $filterId,
                relation_type: RelationType::Component,
                quantity: 2, // 2 filtros
                is_required: true,
                meta: ['position' => 'intake', 'replacement_interval' => '500h'],
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $machineId,
                related_item_id: $valveId,
                relation_type: RelationType::Component,
                quantity: 4, // 4 válvulas
                is_required: true,
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $machineId,
                related_item_id: $beltId,
                relation_type: RelationType::Component,
                quantity: 1,
                is_required: true,
                meta: ['replacement_interval' => '1000h'],
            ),
        ];

        $this->assertEquals($machineId, $components[0]->itemId());
        $this->assertEquals($motorId, $components[0]->relatedItemId());
        $this->assertEquals(RelationType::Component, $components[0]->relationType());
        $this->assertEquals(2, $components[1]->quantity()); // 2 filtros
        $this->assertEquals(4, $components[2]->quantity()); // 4 válvulas
        $this->assertTrue($components[0]->isRequired());
    }

    public function test_spare_part_compatible_with_multiple_machines(): void
    {
        // Un filtro compatible con varias máquinas
        $filterId = $this->generateUuid();
        $filter = new Item(
            id: $filterId,
            name: 'Filtro de Aire FH-500',
            status: ItemStatus::Active,
            workspaceId: $this->workspaceId,
        );

        $machine1Id = $this->generateUuid();
        $machine2Id = $this->generateUuid();
        $machine3Id = $this->generateUuid();

        // El filtro es compatible con múltiples máquinas
        $compatibilities = [
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $filterId,
                related_item_id: $machine1Id,
                relation_type: RelationType::Compatible,
                meta: ['fit' => 'exact'],
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $filterId,
                related_item_id: $machine2Id,
                relation_type: RelationType::Compatible,
                meta: ['fit' => 'exact'],
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $filterId,
                related_item_id: $machine3Id,
                relation_type: RelationType::Compatible,
                meta: ['fit' => 'adapter_required', 'adapter_sku' => 'ADP-001'],
            ),
        ];

        $this->assertEquals(RelationType::Compatible, $compatibilities[0]->relationType());
        $this->assertTrue($compatibilities[0]->relationType()->isBidirectional());
        $this->assertEquals('adapter_required', $compatibilities[2]->meta()['fit']);
    }

    public function test_product_with_accessories(): void
    {
        // Producto principal
        $drillId = $this->generateUuid();
        $drill = new Item(
            id: $drillId,
            name: 'Taladro Percutor 800W',
            status: ItemStatus::Active,
            workspaceId: $this->workspaceId,
        );

        // Accesorios
        $bitSetId = $this->generateUuid();
        $caseId = $this->generateUuid();
        $batteryId = $this->generateUuid();

        $accessories = [
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $drillId,
                related_item_id: $bitSetId,
                relation_type: RelationType::Accessory,
                is_required: false,
                sort_order: 1,
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $drillId,
                related_item_id: $caseId,
                relation_type: RelationType::Accessory,
                is_required: false,
                sort_order: 2,
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $drillId,
                related_item_id: $batteryId,
                relation_type: RelationType::Accessory,
                is_required: false, // Opcional, viene con batería pero puedes comprar extra
                sort_order: 3,
            ),
        ];

        $this->assertEquals(RelationType::Accessory, $accessories[0]->relationType());
        $this->assertFalse($accessories[0]->isRequired()); // Accesorios son opcionales
        $this->assertEquals(1, $accessories[0]->sortOrder());
    }

    public function test_product_requires_another(): void
    {
        // Impresora requiere cartuchos
        $printerId = $this->generateUuid();
        $cartridgeId = $this->generateUuid();

        $requirement = new ItemRelation(
            id: $this->generateUuid(),
            item_id: $printerId,
            related_item_id: $cartridgeId,
            relation_type: RelationType::Requires,
            is_required: true,
            meta: ['consumable' => true],
        );

        $this->assertEquals(RelationType::Requires, $requirement->relationType());
        $this->assertTrue($requirement->isRequired());
        $this->assertFalse($requirement->relationType()->isBidirectional());
    }

    public function test_bundle_kit(): void
    {
        // Kit de herramientas (bundle)
        $toolkitId = $this->generateUuid();
        $toolkit = new Item(
            id: $toolkitId,
            name: 'Kit Básico de Herramientas',
            status: ItemStatus::Active,
            workspaceId: $this->workspaceId,
        );

        // Items que componen el kit
        $hammerId = $this->generateUuid();
        $screwdriverId = $this->generateUuid();
        $pliersId = $this->generateUuid();
        $wrenchId = $this->generateUuid();

        $bundleItems = [
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $toolkitId,
                related_item_id: $hammerId,
                relation_type: RelationType::Bundle,
                quantity: 1,
                is_required: true,
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $toolkitId,
                related_item_id: $screwdriverId,
                relation_type: RelationType::Bundle,
                quantity: 6, // Set de 6 destornilladores
                is_required: true,
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $toolkitId,
                related_item_id: $pliersId,
                relation_type: RelationType::Bundle,
                quantity: 2,
                is_required: true,
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $toolkitId,
                related_item_id: $wrenchId,
                relation_type: RelationType::Bundle,
                quantity: 8, // Juego de 8 llaves
                is_required: true,
            ),
        ];

        $this->assertEquals(RelationType::Bundle, $bundleItems[0]->relationType());
        $this->assertEquals(6, $bundleItems[1]->quantity()); // 6 destornilladores
        $this->assertEquals(8, $bundleItems[3]->quantity()); // 8 llaves
    }

    public function test_replacement_part(): void
    {
        // Modelo antiguo reemplazado por nuevo
        $oldPartId = $this->generateUuid();
        $newPartId = $this->generateUuid();

        $replacement = new ItemRelation(
            id: $this->generateUuid(),
            item_id: $oldPartId,
            related_item_id: $newPartId,
            relation_type: RelationType::Replacement,
            meta: ['reason' => 'discontinued', 'compatibility' => '100%'],
        );

        $this->assertEquals(RelationType::Replacement, $replacement->relationType());
        $this->assertEquals('Reemplaza a', $replacement->relationType()->label());
    }

    public function test_similar_products(): void
    {
        // Productos similares (para "también te puede interesar")
        $product1Id = $this->generateUuid();
        $product2Id = $this->generateUuid();

        $similarity = new ItemRelation(
            id: $this->generateUuid(),
            item_id: $product1Id,
            related_item_id: $product2Id,
            relation_type: RelationType::Similar,
            meta: ['similarity_score' => 0.85],
        );

        $this->assertEquals(RelationType::Similar, $similarity->relationType());
        $this->assertTrue($similarity->relationType()->isBidirectional());
        $this->assertEquals(RelationType::Similar, $similarity->relationType()->inverse());
    }

    public function test_upgrade_path(): void
    {
        // Upgrade de producto (ej: versión básica → pro)
        $basicId = $this->generateUuid();
        $proId = $this->generateUuid();
        $enterpriseId = $this->generateUuid();

        $upgrades = [
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $basicId,
                related_item_id: $proId,
                relation_type: RelationType::Upgrade,
                sort_order: 1,
                meta: ['upgrade_price_diff' => 50],
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $proId,
                related_item_id: $enterpriseId,
                relation_type: RelationType::Upgrade,
                sort_order: 1,
                meta: ['upgrade_price_diff' => 100],
            ),
        ];

        $this->assertEquals(RelationType::Upgrade, $upgrades[0]->relationType());
        $this->assertEquals('Upgrade de', $upgrades[0]->relationType()->label());
    }

    public function test_vehicle_spare_parts_scenario(): void
    {
        // Escenario completo: Vehículo con repuestos
        $vehicleId = $this->generateUuid();
        $vehicle = new Item(
            id: $vehicleId,
            name: 'Toyota Hilux 2024',
            status: ItemStatus::Active,
            workspaceId: $this->workspaceId,
        );

        // Identificadores del vehículo
        $vehicleIdentifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $vehicleId,
            type: IdentifierType::CUSTOM,
            value: 'HILUX-2024-4X4',
            is_primary: true,
        );

        // Repuestos específicos
        $oilFilterId = $this->generateUuid();
        $airFilterId = $this->generateUuid();
        $brakepadId = $this->generateUuid();

        // Componentes originales
        $originalParts = [
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $vehicleId,
                related_item_id: $oilFilterId,
                relation_type: RelationType::Component,
                quantity: 1,
                meta: ['oem' => true, 'part_number' => '04152-YZZA1'],
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $vehicleId,
                related_item_id: $airFilterId,
                relation_type: RelationType::Component,
                quantity: 1,
                meta: ['oem' => true, 'part_number' => '17801-0C010'],
            ),
            new ItemRelation(
                id: $this->generateUuid(),
                item_id: $vehicleId,
                related_item_id: $brakepadId,
                relation_type: RelationType::Component,
                quantity: 4, // 4 juegos de pastillas
                meta: ['oem' => true, 'position' => 'all'],
            ),
        ];

        // Repuesto alternativo compatible
        $aftermarketFilterId = $this->generateUuid();
        $compatible = new ItemRelation(
            id: $this->generateUuid(),
            item_id: $aftermarketFilterId,
            related_item_id: $vehicleId,
            relation_type: RelationType::Compatible,
            meta: ['brand' => 'Mann', 'quality' => 'OEM equivalent'],
        );

        $this->assertEquals('04152-YZZA1', $originalParts[0]->meta()['part_number']);
        $this->assertEquals(4, $originalParts[2]->quantity());
        $this->assertEquals('Mann', $compatible->meta()['brand']);
    }

    public function test_relation_type_labels(): void
    {
        $this->assertEquals('Componente de', RelationType::Component->label());
        $this->assertEquals('Compatible con', RelationType::Compatible->label());
        $this->assertEquals('Accesorio de', RelationType::Accessory->label());
        $this->assertEquals('Reemplaza a', RelationType::Replacement->label());
        $this->assertEquals('Variante de', RelationType::VariantOf->label());
        $this->assertEquals('Parte de bundle', RelationType::Bundle->label());
        $this->assertEquals('Requiere', RelationType::Requires->label());
        $this->assertEquals('Recomendado con', RelationType::Recommended->label());
        $this->assertEquals('Similar a', RelationType::Similar->label());
        $this->assertEquals('Upgrade de', RelationType::Upgrade->label());
    }

    public function test_bidirectional_relations(): void
    {
        // Solo algunas relaciones son bidireccionales por naturaleza
        $this->assertTrue(RelationType::Compatible->isBidirectional());
        $this->assertTrue(RelationType::Similar->isBidirectional());
        
        $this->assertFalse(RelationType::Component->isBidirectional());
        $this->assertFalse(RelationType::Accessory->isBidirectional());
        $this->assertFalse(RelationType::Requires->isBidirectional());
        $this->assertFalse(RelationType::Upgrade->isBidirectional());
    }
}
