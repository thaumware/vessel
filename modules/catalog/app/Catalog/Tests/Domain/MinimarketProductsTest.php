<?php

namespace App\Catalog\Tests\Domain;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Entities\ItemSpecification;
use App\Catalog\Domain\Entities\ItemIdentifier;
use App\Catalog\Domain\ValueObjects\ItemStatus;
use App\Catalog\Domain\ValueObjects\IdentifierType;
use App\Catalog\Domain\ValueObjects\SpecDataType;
use App\Catalog\Tests\CatalogTestCase;

/**
 * Test: Catálogo de Productos Minimarket
 * 
 * Características:
 * - Items son productos físicos
 * - Tienen SKU, EAN/barcode
 * - UoM importante (unidad, kg, litro)
 * - Especificaciones: peso, volumen, contenido, origen
 * - Stock físico real
 */
class MinimarketProductsTest extends CatalogTestCase
{
    private string $workspaceId;
    private string $uomUnitId;
    private string $uomKgId;
    private string $uomLiterId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workspaceId = $this->generateUuid();
        // UoM IDs simulados (vendrían del módulo Uom)
        $this->uomUnitId = $this->generateUuid();
        $this->uomKgId = $this->generateUuid();
        $this->uomLiterId = $this->generateUuid();
    }

    public function test_create_unit_product(): void
    {
        $productId = $this->generateUuid();
        
        $product = new Item(
            id: $productId,
            name: 'Coca-Cola 500ml',
            status: ItemStatus::Active,
            description: 'Bebida gaseosa Coca-Cola botella plástica 500ml',
            workspaceId: $this->workspaceId,
            uomId: $this->uomUnitId, // Se vende por unidad
        );

        $this->assertEquals('Coca-Cola 500ml', $product->getName());
        $this->assertEquals($this->uomUnitId, $product->getUomId());
    }

    public function test_product_with_multiple_identifiers(): void
    {
        $productId = $this->generateUuid();

        // Un producto puede tener múltiples identificadores
        $identifiers = [
            new ItemIdentifier(
                id: $this->generateUuid(),
                item_id: $productId,
                type: IdentifierType::SKU,
                value: 'BEB-COCA-500',
                is_primary: true,
            ),
            new ItemIdentifier(
                id: $this->generateUuid(),
                item_id: $productId,
                type: IdentifierType::EAN,
                value: '7702004003218',
                is_primary: false,
            ),
            new ItemIdentifier(
                id: $this->generateUuid(),
                item_id: $productId,
                type: IdentifierType::SUPPLIER,
                value: 'COCA-500-PET',
                is_primary: false,
            ),
        ];

        $this->assertEquals('BEB-COCA-500', $identifiers[0]->value());
        $this->assertEquals('7702004003218', $identifiers[1]->value());
        $this->assertTrue($identifiers[0]->isPrimary());
    }

    public function test_product_with_specifications(): void
    {
        $productId = $this->generateUuid();

        $specs = [
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $productId,
                key: 'volume',
                value: '500',
                data_type: SpecDataType::Number,
                unit: 'ml',
                group: 'physical',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $productId,
                key: 'weight',
                value: '520',
                data_type: SpecDataType::Number,
                unit: 'g',
                group: 'physical',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $productId,
                key: 'container',
                value: 'PET',
                data_type: SpecDataType::String,
                group: 'packaging',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $productId,
                key: 'refrigerated',
                value: 'false',
                data_type: SpecDataType::Boolean,
                group: 'storage',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $productId,
                key: 'origin_country',
                value: 'Chile',
                data_type: SpecDataType::String,
                group: 'origin',
            ),
        ];

        $this->assertEquals(500, $specs[0]->typedValue());
        $this->assertEquals('ml', $specs[0]->unit());
        $this->assertFalse($specs[3]->typedValue());
    }

    public function test_weighted_product(): void
    {
        // Productos que se venden por peso (frutas, carnes, etc.)
        $productId = $this->generateUuid();

        $product = new Item(
            id: $productId,
            name: 'Manzana Royal Gala',
            status: ItemStatus::Active,
            description: 'Manzana Royal Gala granel',
            workspaceId: $this->workspaceId,
            uomId: $this->uomKgId, // Se vende por kg
        );

        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $productId,
            type: IdentifierType::SKU,
            value: 'FRU-MANZ-RG',
            is_primary: true,
        );

        // PLU code para balanzas
        $pluIdentifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $productId,
            type: IdentifierType::CUSTOM,
            value: '4135', // PLU estándar para manzanas
            is_primary: false,
        );

        $this->assertEquals($this->uomKgId, $product->getUomId());
        $this->assertEquals('4135', $pluIdentifier->value());
    }

    public function test_product_with_variants_sizes(): void
    {
        // Producto con variantes (ej: bebida en diferentes tamaños)
        $baseProductId = $this->generateUuid();
        $variant330Id = $this->generateUuid();
        $variant500Id = $this->generateUuid();
        $variant1500Id = $this->generateUuid();

        // Producto base
        $baseProduct = new Item(
            id: $baseProductId,
            name: 'Coca-Cola',
            status: ItemStatus::Active,
            workspaceId: $this->workspaceId,
            uomId: $this->uomUnitId,
        );

        // Specs por variante
        $variant330Specs = [
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $baseProductId,
                variant_id: $variant330Id,
                key: 'size',
                value: '330',
                data_type: SpecDataType::Number,
                unit: 'ml',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $baseProductId,
                variant_id: $variant330Id,
                key: 'container',
                value: 'lata',
                data_type: SpecDataType::String,
            ),
        ];

        $variant1500Specs = [
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $baseProductId,
                variant_id: $variant1500Id,
                key: 'size',
                value: '1500',
                data_type: SpecDataType::Number,
                unit: 'ml',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $baseProductId,
                variant_id: $variant1500Id,
                key: 'container',
                value: 'botella',
                data_type: SpecDataType::String,
            ),
        ];

        $this->assertEquals(330, $variant330Specs[0]->typedValue());
        $this->assertEquals(1500, $variant1500Specs[0]->typedValue());
        $this->assertEquals('lata', $variant330Specs[1]->typedValue());
        $this->assertEquals('botella', $variant1500Specs[1]->typedValue());
    }

    public function test_draft_product_not_for_sale(): void
    {
        $productId = $this->generateUuid();

        $product = new Item(
            id: $productId,
            name: 'Producto Nuevo (en revisión)',
            status: ItemStatus::Draft,
            workspaceId: $this->workspaceId,
        );

        $this->assertTrue($product->getStatus()->isDraft());
        $this->assertFalse($product->getStatus()->isActive());
    }

    public function test_archived_discontinued_product(): void
    {
        $productId = $this->generateUuid();

        $product = new Item(
            id: $productId,
            name: 'Producto Descontinuado',
            status: ItemStatus::Archived,
            workspaceId: $this->workspaceId,
        );

        $this->assertTrue($product->getStatus()->isArchived());
    }
}
