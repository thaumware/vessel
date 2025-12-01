<?php

namespace App\Taxonomy\Tests\UseCases;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use App\Taxonomy\Domain\UseCases\Term\CreateTerm;
use App\Taxonomy\Tests\TaxonomyTestCase;

class CreateTermTest extends TaxonomyTestCase
{
    /** @var TermRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private CreateTerm $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = $this->createMock(TermRepositoryInterface::class);
        $this->useCase = new CreateTerm($this->repository);
    }

    public function test_can_create_term(): void
    {
        $termId = $this->generateUuid();
        $vocabularyId = $this->generateUuid();

        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Term $term) use ($termId, $vocabularyId) {
                return $term->getId() === $termId
                    && $term->getName() === 'Test Term'
                    && $term->getSlug() === 'test-term'
                    && $term->getVocabularyId() === $vocabularyId;
            }));

        $result = $this->useCase->execute(
            id: $termId,
            name: 'Test Term',
            vocabularyId: $vocabularyId,
            description: 'A test term'
        );

        $this->assertInstanceOf(Term::class, $result);
        $this->assertEquals($termId, $result->getId());
        $this->assertEquals('Test Term', $result->getName());
        $this->assertEquals('test-term', $result->getSlug());
        $this->assertEquals('A test term', $result->getDescription());
    }

    public function test_generates_unique_slug_when_exists(): void
    {
        $vocabularyId = $this->generateUuid();
        $existingTerm = new Term(
            id: $this->generateUuid(),
            name: 'Existing',
            slug: 'test-term',
            vocabularyId: $vocabularyId
        );

        // First call returns existing term, second returns null
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturnCallback(function ($slug, $vocabId) use ($existingTerm) {
                if ($slug === 'test-term') {
                    return $existingTerm;
                }
                return null;
            });

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Test Term',
            vocabularyId: $vocabularyId
        );

        $this->assertEquals('test-term-1', $result->getSlug());
    }

    public function test_generates_slug_with_incremented_number(): void
    {
        $vocabularyId = $this->generateUuid();

        // Simulate test-term and test-term-1 already exist
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturnCallback(function ($slug, $vocabId) use ($vocabularyId) {
                if ($slug === 'test-term' || $slug === 'test-term-1') {
                    return new Term(
                        id: $this->generateUuid(),
                        name: 'Existing',
                        slug: $slug,
                        vocabularyId: $vocabularyId
                    );
                }
                return null;
            });

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Test Term',
            vocabularyId: $vocabularyId
        );

        $this->assertEquals('test-term-2', $result->getSlug());
    }

    public function test_generates_slug_from_name_with_special_characters(): void
    {
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturn(null);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Test & Term (Special)',
            vocabularyId: $this->generateUuid()
        );

        $this->assertEquals('test-term-special', $result->getSlug());
    }

    public function test_creates_term_without_description(): void
    {
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturn(null);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Simple Term',
            vocabularyId: $this->generateUuid()
        );

        $this->assertNull($result->getDescription());
    }

    public function test_slug_handles_uppercase(): void
    {
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturn(null);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'UPPERCASE TERM',
            vocabularyId: $this->generateUuid()
        );

        $this->assertEquals('uppercase-term', $result->getSlug());
    }

    public function test_handles_very_short_name(): void
    {
        $vocabularyId = $this->generateUuid();

        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturn(null);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'ab',
            vocabularyId: $vocabularyId
        );

        $this->assertEquals('ab', $result->getSlug());
    }

    public function test_handles_name_with_only_special_characters(): void
    {
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturn(null);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: '!!!@@@###',
            vocabularyId: $this->generateUuid()
        );

        // Should fall back to 'term' when slug is empty
        $this->assertEquals('term', $result->getSlug());
    }

    public function test_handles_name_with_unicode_characters(): void
    {
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturn(null);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Categoría Española',
            vocabularyId: $this->generateUuid()
        );

        // Non-ASCII characters are removed
        $this->assertEquals('categor-a-espa-ola', $result->getSlug());
    }

    public function test_handles_name_with_multiple_spaces(): void
    {
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturn(null);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Multiple   Spaces   Here',
            vocabularyId: $this->generateUuid()
        );

        $this->assertEquals('multiple-spaces-here', $result->getSlug());
    }

    public function test_handles_name_with_leading_trailing_spaces(): void
    {
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturn(null);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: '  Trimmed Name  ',
            vocabularyId: $this->generateUuid()
        );

        $this->assertEquals('trimmed-name', $result->getSlug());
    }

    public function test_unique_slug_with_many_existing(): void
    {
        $vocabularyId = $this->generateUuid();

        // Simulate ter, ter-1, ter-2, ..., ter-10 already exist
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturnCallback(function ($slug, $vocabId) use ($vocabularyId) {
                if ($slug === 'ter' || preg_match('/^ter-([1-9]|10)$/', $slug)) {
                    return new Term(
                        id: $this->generateUuid(),
                        name: 'Existing',
                        slug: $slug,
                        vocabularyId: $vocabularyId
                    );
                }
                return null;
            });

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'ter',
            vocabularyId: $vocabularyId
        );

        $this->assertEquals('ter-11', $result->getSlug());
    }

    /**
     * Test that simulates the soft-deleted scenario:
     * The repository should return soft-deleted terms when checking for slug uniqueness
     * to avoid unique constraint violations at the database level.
     * 
     * In production, findBySlugAndVocabulary includes withTrashed() to find
     * soft-deleted terms and generate a unique slug suffix.
     */
    public function test_generates_unique_slug_when_existing_term_is_soft_deleted(): void
    {
        $vocabularyId = $this->generateUuid();
        
        // Simulate a soft-deleted term with slug 'archived-term'
        // The repository (with withTrashed) should return this term
        $softDeletedTerm = new Term(
            id: $this->generateUuid(),
            name: 'Archived Term',
            slug: 'archived-term',
            vocabularyId: $vocabularyId
        );

        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturnCallback(function ($slug, $vocabId) use ($softDeletedTerm) {
                // Simulates withTrashed() behavior - returns soft-deleted term
                if ($slug === 'archived-term') {
                    return $softDeletedTerm;
                }
                return null;
            });

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Archived Term',
            vocabularyId: $vocabularyId
        );

        // Should generate unique slug because soft-deleted term has same slug
        $this->assertEquals('archived-term-1', $result->getSlug());
    }

    public function test_falls_back_to_timestamp_slug_when_max_attempts_exceeded(): void
    {
        $vocabularyId = $this->generateUuid();
        $callCount = 0;

        // All slugs with counter already exist - will use timestamp fallback
        $this->repository
            ->method('findBySlugAndVocabulary')
            ->willReturnCallback(function ($slug, $vocabId) use ($vocabularyId, &$callCount) {
                $callCount++;
                // Return existing term only for first 101 attempts (base + 1-100)
                // After that (timestamp slug), return null to allow creation
                if ($callCount <= 101) {
                    return new Term(
                        id: $this->generateUuid(),
                        name: 'Existing',
                        slug: $slug,
                        vocabularyId: $vocabularyId
                    );
                }
                return null;
            });

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Test',
            vocabularyId: $vocabularyId
        );

        // Should have timestamp-based slug (test-TIMESTAMP-RANDOM format)
        $this->assertMatchesRegularExpression('/^test-\d+-\d+$/', $result->getSlug());
    }
}
