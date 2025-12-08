<?php

declare(strict_types=1);

namespace App\Catalog\Tests\Domain;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Entities\ItemSpecification;
use App\Catalog\Domain\ValueObjects\ItemStatus;
use App\Catalog\Domain\ValueObjects\SpecDataType;
use App\Catalog\Tests\CatalogTestCase;
use Ramsey\Uuid\Uuid;

/**
 * Test: Catalogo de Razas/Especies de Animales
 * 
 * IMPORTANTE: El catalogo define TIPOS (razas, especies), NO individuos.
 * - Catalogo: "Golden Retriever" (la raza con caracteristicas tipicas)
 * - Inventario: "Max, Golden Retriever macho, 3 meses, chip #123456" (un animal especifico)
 */
class AnimalCatalogTest extends CatalogTestCase
{
    public function test_dog_breed_catalog_item(): void
    {
        $item = new Item(
            id: Uuid::uuid4()->toString(),
            workspaceId: Uuid::uuid4()->toString(),
            name: 'Golden Retriever',
            description: 'Raza de perro mediano-grande, conocida por su temperamento amigable.',
            status: ItemStatus::Active
        );

        $specs = [
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'breed_group',
                value: 'Sporting / Retriever',
                data_type: SpecDataType::String,
                sort_order: 1
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'typical_weight_kg',
                value: json_encode(['min' => 25, 'max' => 34]),
                data_type: SpecDataType::Json,
                sort_order: 2
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'life_expectancy_years',
                value: json_encode(['min' => 10, 'max' => 12]),
                data_type: SpecDataType::Json,
                sort_order: 3
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'akc_recognized',
                value: 'true',
                data_type: SpecDataType::Boolean,
                sort_order: 4
            ),
        ];

        $this->assertEquals('Golden Retriever', $item->getName());
        $this->assertEquals(ItemStatus::Active, $item->getStatus());
        $this->assertCount(4, $specs);
        $this->assertEquals('breed_group', $specs[0]->key());
    }

    public function test_cattle_breed_catalog_item(): void
    {
        $item = new Item(
            id: Uuid::uuid4()->toString(),
            workspaceId: Uuid::uuid4()->toString(),
            name: 'Aberdeen Angus',
            description: 'Raza de ganado bovino reconocida por la calidad de su carne.',
            status: ItemStatus::Active
        );

        $specs = [
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'species',
                value: 'Bos taurus',
                data_type: SpecDataType::String,
                sort_order: 1
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'purpose',
                value: json_encode(['Carne']),
                data_type: SpecDataType::Json,
                sort_order: 2
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'typical_weight_kg',
                value: json_encode(['bull' => ['min' => 850, 'max' => 1100], 'cow' => ['min' => 500, 'max' => 700]]),
                data_type: SpecDataType::Json,
                sort_order: 3
            ),
        ];

        $this->assertEquals('Aberdeen Angus', $item->getName());
        $this->assertCount(3, $specs);
    }

    public function test_exotic_bird_species_with_cites(): void
    {
        $item = new Item(
            id: Uuid::uuid4()->toString(),
            workspaceId: Uuid::uuid4()->toString(),
            name: 'Ara ararauna (Guacamayo Azul y Amarillo)',
            description: 'Especie de guacamayo nativa de America del Sur.',
            status: ItemStatus::Active
        );

        $specs = [
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'scientific_name',
                value: 'Ara ararauna',
                data_type: SpecDataType::String,
                sort_order: 1
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'cites_appendix',
                value: 'II',
                data_type: SpecDataType::String,
                sort_order: 2
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'requires_cites_permit',
                value: 'true',
                data_type: SpecDataType::Boolean,
                sort_order: 3
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'lifespan_years',
                value: json_encode(['min' => 30, 'max' => 50]),
                data_type: SpecDataType::Json,
                sort_order: 4
            ),
        ];

        $this->assertEquals('Ara ararauna (Guacamayo Azul y Amarillo)', $item->getName());
        $this->assertEquals('cites_appendix', $specs[1]->key());
        $this->assertEquals('II', $specs[1]->value());
    }

    public function test_cat_breed_catalog_item(): void
    {
        $item = new Item(
            id: Uuid::uuid4()->toString(),
            workspaceId: Uuid::uuid4()->toString(),
            name: 'Maine Coon',
            description: 'Una de las razas de gatos domesticos mas grandes.',
            status: ItemStatus::Active
        );

        $specs = [
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'origin',
                value: 'Estados Unidos (Maine)',
                data_type: SpecDataType::String,
                sort_order: 1
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'typical_weight_kg',
                value: json_encode(['male' => ['min' => 6, 'max' => 11], 'female' => ['min' => 4, 'max' => 7]]),
                data_type: SpecDataType::Json,
                sort_order: 2
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'hypoallergenic',
                value: 'false',
                data_type: SpecDataType::Boolean,
                sort_order: 3
            ),
        ];

        $this->assertEquals('Maine Coon', $item->getName());
        $this->assertCount(3, $specs);
    }

    public function test_ornamental_fish_species(): void
    {
        $item = new Item(
            id: Uuid::uuid4()->toString(),
            workspaceId: Uuid::uuid4()->toString(),
            name: 'Betta splendens (Pez Betta)',
            description: 'Pez de agua dulce popular en acuarismo.',
            status: ItemStatus::Active
        );

        $specs = [
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'scientific_name',
                value: 'Betta splendens',
                data_type: SpecDataType::String,
                sort_order: 1
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'water_type',
                value: 'Dulce',
                data_type: SpecDataType::String,
                sort_order: 2
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'temperature_celsius',
                value: json_encode(['min' => 24, 'max' => 28]),
                data_type: SpecDataType::Json,
                sort_order: 3
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'tank_size_liters_min',
                value: '10',
                data_type: SpecDataType::Number,
                sort_order: 4
            ),
        ];

        $this->assertEquals('Betta splendens (Pez Betta)', $item->getName());
        $tempSpec = $specs[2];
        $tempRange = json_decode($tempSpec->value(), true);
        $this->assertEquals(24, $tempRange['min']);
        $this->assertEquals(28, $tempRange['max']);
    }

    public function test_new_breed_pending_approval(): void
    {
        $item = new Item(
            id: Uuid::uuid4()->toString(),
            workspaceId: Uuid::uuid4()->toString(),
            name: 'Pomsky',
            description: 'Raza hibrida Pomeranian x Husky. No reconocida por AKC/FCI.',
            status: ItemStatus::Draft
        );

        $specs = [
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'parent_breeds',
                value: json_encode(['Pomeranian', 'Siberian Husky']),
                data_type: SpecDataType::Json,
                sort_order: 1
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'akc_recognized',
                value: 'false',
                data_type: SpecDataType::Boolean,
                sort_order: 2
            ),
        ];

        $this->assertEquals(ItemStatus::Draft, $item->getStatus());
        $this->assertEquals('Pomsky', $item->getName());
        $this->assertEquals('false', $specs[1]->value());
    }

    public function test_archived_breed_no_longer_bred(): void
    {
        $item = new Item(
            id: Uuid::uuid4()->toString(),
            workspaceId: Uuid::uuid4()->toString(),
            name: 'English White Terrier',
            description: 'Raza extinta de terrier. Desaparecio a principios del siglo XX.',
            status: ItemStatus::Archived
        );

        $specs = [
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'extinction_year',
                value: '1920',
                data_type: SpecDataType::Number,
                sort_order: 1
            ),
            new ItemSpecification(
                id: Uuid::uuid4()->toString(),
                item_id: $item->getId(),
                key: 'contributed_to_breeds',
                value: json_encode(['Bull Terrier', 'Boston Terrier']),
                data_type: SpecDataType::Json,
                sort_order: 2
            ),
        ];

        $this->assertEquals(ItemStatus::Archived, $item->getStatus());
        $this->assertCount(2, $specs);
    }
}
