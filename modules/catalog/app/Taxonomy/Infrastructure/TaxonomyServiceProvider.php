<?php

namespace App\Taxonomy\Infrastructure;

use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;
use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;
use App\Taxonomy\Infrastructure\Out\Models\Eloquent\TermRepository;
use App\Taxonomy\Infrastructure\Out\Models\Eloquent\VocabularyRepository;
use Illuminate\Support\ServiceProvider;

class TaxonomyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repositories (default: Eloquent)
        $this->app->bind(
            VocabularyRepositoryInterface::class,
            VocabularyRepository::class
        );

        $this->app->bind(
            TermRepositoryInterface::class,
            TermRepository::class
        );

        // === Adapter Configuration for Middleware ===
        $this->app->instance('adapters.taxonomy', [
            'interfaces' => [
                TermRepositoryInterface::class => [
                    'local' => \App\Taxonomy\Infrastructure\Out\InMemory\InMemoryTermRepository::class,
                    'eloquent' => TermRepository::class,
                ],
                VocabularyRepositoryInterface::class => [
                    'local' => \App\Taxonomy\Infrastructure\Out\InMemory\InMemoryVocabularyRepository::class,
                    'eloquent' => VocabularyRepository::class,
                ],
            ],
        ]);
    }

    public function boot(): void
    {
        // Register middleware alias
        $this->app['router']->aliasMiddleware('taxonomy_adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':taxonomy');

        // Register migrations from Taxonomy module
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Register routes from Taxonomy module
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/TaxonomyRoutes.php');
    }
}
