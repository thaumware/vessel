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
 * Test: Catálogo de Servicios Freelance (tipo Fiverr)
 * 
 * Características:
 * - Items son servicios (diseño, programación, traducciones)
 * - No tienen SKU tradicional, pero sí identificadores únicos
 * - Especificaciones: duración, entregas, revisiones, complejidad
 * - Sin stock físico (pero podrían tener "slots" disponibles)
 */
class FreelanceServicesTest extends CatalogTestCase
{
    public function test_create_freelance_service_item(): void
    {
        $serviceId = $this->generateUuid();

        $service = new Item(
            id: $serviceId,
            name: 'Diseño de Logo Profesional',
            status: ItemStatus::Active,
            description: 'Diseño de logo con 3 conceptos iniciales y revisiones ilimitadas',
            workspaceId: $this->generateUuid(),
            uomId: null, // Servicios no requieren UoM tradicional
        );

        $this->assertEquals('Diseño de Logo Profesional', $service->getName());
        $this->assertTrue($service->getStatus()->isActive());
        $this->assertNull($service->getUomId());
    }

    public function test_service_with_specifications(): void
    {
        $serviceId = $this->generateUuid();
        $workspaceId = $this->generateUuid();

        // Especificaciones típicas de un servicio freelance
        $specs = [
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $serviceId,
                key: 'delivery_time',
                value: '3',
                data_type: SpecDataType::Number,
                unit: 'days',
                group: 'delivery',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $serviceId,
                key: 'revisions',
                value: 'unlimited',
                data_type: SpecDataType::String,
                group: 'delivery',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $serviceId,
                key: 'concepts_included',
                value: '3',
                data_type: SpecDataType::Number,
                group: 'deliverables',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $serviceId,
                key: 'source_files',
                value: 'true',
                data_type: SpecDataType::Boolean,
                group: 'deliverables',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $serviceId,
                key: 'formats',
                value: '["PNG", "SVG", "PDF", "AI"]',
                data_type: SpecDataType::Json,
                group: 'deliverables',
            ),
        ];

        // Verificar specs
        $this->assertEquals(3, $specs[0]->typedValue()); // Number cast
        $this->assertEquals('unlimited', $specs[1]->typedValue());
        $this->assertTrue($specs[3]->typedValue()); // Boolean cast
        $this->assertIsArray($specs[4]->typedValue()); // JSON cast
        $this->assertContains('SVG', $specs[4]->typedValue());
    }

    public function test_service_with_custom_identifier(): void
    {
        $serviceId = $this->generateUuid();

        // En plataformas tipo Fiverr, los servicios tienen un slug/gig_id
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $serviceId,
            type: IdentifierType::CUSTOM,
            value: 'logo-design-pro-3-concepts',
            is_primary: true,
        );

        $this->assertEquals('logo-design-pro-3-concepts', $identifier->value());
        $this->assertTrue($identifier->isPrimary());
    }

    public function test_service_tiers_as_variants(): void
    {
        // Los "tiers" (básico, estándar, premium) serían variantes
        // Cada variante tiene sus propias specs

        $serviceId = $this->generateUuid();
        $basicVariantId = $this->generateUuid();
        $premiumVariantId = $this->generateUuid();

        // Specs para tier básico
        $basicSpecs = [
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $serviceId,
                variant_id: $basicVariantId,
                key: 'delivery_time',
                value: '7',
                data_type: SpecDataType::Number,
                unit: 'days',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $serviceId,
                variant_id: $basicVariantId,
                key: 'revisions',
                value: '2',
                data_type: SpecDataType::Number,
            ),
        ];

        // Specs para tier premium
        $premiumSpecs = [
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $serviceId,
                variant_id: $premiumVariantId,
                key: 'delivery_time',
                value: '2',
                data_type: SpecDataType::Number,
                unit: 'days',
            ),
            new ItemSpecification(
                id: $this->generateUuid(),
                item_id: $serviceId,
                variant_id: $premiumVariantId,
                key: 'revisions',
                value: 'unlimited',
                data_type: SpecDataType::String,
            ),
        ];

        // Basic: 7 días, 2 revisiones
        $this->assertEquals(7, $basicSpecs[0]->typedValue());
        $this->assertEquals(2, $basicSpecs[1]->typedValue());

        // Premium: 2 días, ilimitadas
        $this->assertEquals(2, $premiumSpecs[0]->typedValue());
        $this->assertEquals('unlimited', $premiumSpecs[1]->typedValue());
    }
}
