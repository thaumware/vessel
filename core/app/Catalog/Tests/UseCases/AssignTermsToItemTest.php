<?php

namespace App\Catalog\Tests\UseCases;

use App\Catalog\Domain\Entities\ItemClassification;
use App\Catalog\Domain\Interfaces\ItemClassificationRepositoryInterface;
use App\Catalog\Domain\Interfaces\TaxonomyGatewayInterface;
use App\Catalog\Domain\UseCases\AssignTermsToItem;
use App\Catalog\Tests\CatalogTestCase;
use DomainException;

class AssignTermsToItemTest extends CatalogTestCase
{
    private FakeClassificationRepository $repo;
    private AssignTermsToItem $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new FakeClassificationRepository();
    }

    public function test_assigns_animal_taxonomy_with_paths(): void
    {
        $gateway = new FakeTaxonomyGateway([
            'animals' => [
                'lion' => 'kingdom/animalia/phylum/chordata/class/mammalia/order/carnivora/family/felidae/genus/panthera',
                'mammal' => 'kingdom/animalia/phylum/chordata/class/mammalia',
            ],
        ]);

        $this->useCase = new AssignTermsToItem($this->repo, $gateway);

        $itemId = $this->generateUuid();
        $this->useCase->execute($itemId, 'animals', ['lion', 'mammal']);

        $saved = $this->repo->findByItem($itemId);
        $this->assertCount(2, $saved);
        $this->assertEquals('lion', $saved[0]->termId);
        $this->assertStringContainsString('panthera', $saved[0]->ancestryPath);
        $this->assertEquals('animals', $saved[0]->vocabularyId);
    }

    public function test_assigns_product_taxonomy_without_paths(): void
    {
        $gateway = new FakeTaxonomyGateway([
            'products' => [
                'notebook' => null,
                'electronics' => null,
            ],
        ]);

        $this->useCase = new AssignTermsToItem($this->repo, $gateway);

        $itemId = $this->generateUuid();
        $this->useCase->execute($itemId, 'products', ['notebook', 'electronics']);

        $saved = $this->repo->findByItem($itemId);
        $this->assertCount(2, $saved);
        $this->assertNull($saved[0]->ancestryPath);
        $this->assertNull($saved[1]->ancestryPath);
    }

    public function test_fails_when_term_missing(): void
    {
        $gateway = new FakeTaxonomyGateway([
            'animals' => [
                'lion' => 'kingdom/animalia/phylum/chordata/class/mammalia/order/carnivora/family/felidae/genus/panthera',
            ],
        ]);

        $this->useCase = new AssignTermsToItem($this->repo, $gateway);

        $this->expectException(DomainException::class);
        $this->useCase->execute($this->generateUuid(), 'animals', ['lion', 'tiger']);
    }
}

/**
 * Fakes para tests del use case.
 */
final class FakeTaxonomyGateway implements TaxonomyGatewayInterface
{
    /** @param array<string, array<string, string|null>> $map */
    public function __construct(private array $map) {}

    public function getTermPaths(string $vocabularyId, array $termIds): array
    {
        $vocabulary = $this->map[$vocabularyId] ?? [];
        $result = [];

        foreach ($termIds as $termId) {
            if (!array_key_exists($termId, $vocabulary)) {
                throw new DomainException("Term {$termId} not found in vocabulary {$vocabularyId}");
            }
            $result[$termId] = $vocabulary[$termId];
        }

        return $result;
    }
}

final class FakeClassificationRepository implements ItemClassificationRepositoryInterface
{
    /** @var array<string, ItemClassification[]> */
    private array $byItem = [];

    public function replaceForItem(string $itemId, array $classifications): void
    {
        $this->byItem[$itemId] = $classifications;
    }

    public function findByItem(string $itemId): array
    {
        return $this->byItem[$itemId] ?? [];
    }
}
