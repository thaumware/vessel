<?php

return [
    'modules' => [
        'stock' => [
            'enabled' => env('MODULE_STOCK_ENABLED', true),
            'ws_enabled' => env('MODULE_STOCK_WS_ENABLED', false),
            'provider' => \App\Stock\Infrastructure\StockServiceProvider::class,
            'migrations_path' => base_path('app/Stock/Infrastructure/Out/Database/Migrations'),
        ],
        'locations' => [
            'enabled' => env('MODULE_LOCATIONS_ENABLED', true),
            'ws_enabled' => env('MODULE_LOCATIONS_WS_ENABLED', false),
            'provider' => \App\Locations\Infrastructure\LocationsServiceProvider::class,
            'migrations_path' => base_path('app/Locations/Infrastructure/Out/Database/Migrations'),
        ],
        'taxonomy' => [
            'enabled' => env('MODULE_TAXONOMY_ENABLED', true),
            'ws_enabled' => env('MODULE_TAXONOMY_WS_ENABLED', false),
            'provider' => \App\Taxonomy\Infrastructure\TaxonomyServiceProvider::class,
            'migrations_path' => base_path('app/Taxonomy/Infrastructure/Out/Database/Migrations'),
        ],
        'uom' => [
            'enabled' => env('MODULE_UOM_ENABLED', true),
            'ws_enabled' => env('MODULE_UOM_WS_ENABLED', false),
            'provider' => \App\Uom\Infrastructure\UomServiceProvider::class,
            'migrations_path' => base_path('app/Uom/Infrastructure/Out/Database/Migrations'),
        ],
        'pricing' => [
            'enabled' => env('MODULE_PRICING_ENABLED', true),
            'ws_enabled' => env('MODULE_PRICING_WS_ENABLED', false),
            'provider' => \App\Pricing\Infrastructure\PricingServiceProvider::class,
            'migrations_path' => base_path('app/Pricing/Infrastructure/Out/Database/Migrations'),
        ],
        'catalog' => [
            'enabled' => env('MODULE_CATALOG_ENABLED', true),
            'ws_enabled' => env('MODULE_CATALOG_WS_ENABLED', false),
            'provider' => \App\Catalog\Infrastructure\CatalogServiceProvider::class,
            'migrations_path' => base_path('app/Catalog/Infrastructure/Out/Database/Migrations'),
        ],
        'auth' => [
            'enabled' => env('MODULE_AUTH_ENABLED', true),
            'ws_enabled' => env('MODULE_AUTH_WS_ENABLED', false),
            'provider' => \App\Auth\Infrastructure\AuthServiceProvider::class,
            'migrations_path' => base_path('app/Auth/Infrastructure/Out/Database/Migrations'),
        ],
        'portal' => [
            'enabled' => env('MODULE_PORTAL_ENABLED', true),
            'ws_enabled' => env('MODULE_PORTAL_WS_ENABLED', false),
            'provider' => \App\Shared\Providers\PortalServiceProvider::class,
            'migrations_path' => base_path('app/Shared/Infrastructure/Out/Database/Migrations'),
        ],
        // Add more modules here as they become plugins
    ],
];
