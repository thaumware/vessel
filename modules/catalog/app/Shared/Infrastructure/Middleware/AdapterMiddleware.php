<?php

namespace App\Shared\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdapterMiddleware
{
    private array $moduleConfigs = [
        'locations' => [
            'interface' => \App\Locations\Domain\Interfaces\LocationRepository::class,
            'eloquent' => \App\Locations\Infrastructure\Out\Models\Eloquent\EloquentLocationRepository::class,
            'inmemory' => \App\Locations\Infrastructure\Out\InMemory\InMemoryLocationRepository::class,
        ],
        'taxonomy' => [
            'interfaces' => [
                \App\Taxonomy\Domain\Interfaces\TermRepositoryInterface::class,
                \App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface::class,
            ],
            'eloquent' => [
                \App\Taxonomy\Domain\Interfaces\TermRepositoryInterface::class => \App\Taxonomy\Infrastructure\Out\Models\Eloquent\TermRepository::class,
                \App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface::class => \App\Taxonomy\Infrastructure\Out\Models\Eloquent\VocabularyRepository::class,
            ],
            'inmemory' => [
                \App\Taxonomy\Domain\Interfaces\TermRepositoryInterface::class => \App\Taxonomy\Infrastructure\Out\InMemory\InMemoryTermRepository::class,
                \App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface::class => \App\Taxonomy\Infrastructure\Out\InMemory\InMemoryVocabularyRepository::class,
            ],
        ],
    ];

    public function handle(Request $request, Closure $next, string $module)
    {
        $adapter = $request->header('X-' . strtoupper($module) . '-ADAPTER', 'eloquent');

        if (!isset($this->moduleConfigs[$module])) {
            return $next($request);
        }

        $config = $this->moduleConfigs[$module];

        if ($adapter === 'local' && isset($config['inmemory'])) {
            if (isset($config['interfaces'])) {
                // Multiple interfaces (like Taxonomy)
                foreach ($config['interfaces'] as $interface) {
                    if (isset($config['inmemory'][$interface])) {
                        app()->bind($interface, $config['inmemory'][$interface]);
                    }
                }
            } else {
                // Single interface (like Locations)
                app()->bind($config['interface'], $config['inmemory']);
            }
        } else {
            if (isset($config['interfaces'])) {
                // Multiple interfaces (like Taxonomy)
                foreach ($config['interfaces'] as $interface) {
                    if (isset($config['eloquent'][$interface])) {
                        app()->bind($interface, $config['eloquent'][$interface]);
                    }
                }
            } else {
                // Single interface (like Locations)
                app()->bind($config['interface'], $config['eloquent']);
            }
        }

        return $next($request);
    }
}