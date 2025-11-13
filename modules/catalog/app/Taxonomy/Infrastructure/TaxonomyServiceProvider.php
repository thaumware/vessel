<?php

namespace App\Taxonomy\Infrastructure;

use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;
use App\Taxonomy\Infrastructure\Out\Models\Eloquent\TermRepository;
use App\Taxonomy\Infrastructure\Out\Models\Eloquent\VocabularyRepository;
use Illuminate\Support\ServiceProvider;

class TaxonomyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(
            VocabularyRepositoryInterface::class,
            VocabularyRepository::class
        );

        $this->app->bind(
            TermRepositoryInterface::class,
            TermRepository::class
        );
    }

    public function boot(): void
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('taxonomy_adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':taxonomy');

        // Register migrations from Taxonomy module (relative to this file)
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');

        // Register routes from Taxonomy module
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/TaxonomyRoutes.php');
    }
}
