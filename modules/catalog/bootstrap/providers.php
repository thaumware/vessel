<?php

use App\AppServiceProvider;
use App\Catalog\Infrastructure\CatalogServiceProvider;
use App\Shared\Providers\PortalServiceProvider;
use App\Shared\Providers\HealthServiceProvider;
use App\Auth\Infrastructure\AuthServiceProvider;
use App\Locations\Infrastructure\LocationsServiceProvider;
use App\Pricing\Infrastructure\PricingServiceProvider;
use App\Stock\Infrastructure\StockServiceProvider;
use App\Taxonomy\Infrastructure\TaxonomyServiceProvider;
use App\Uom\Infrastructure\UomServiceProvider;

return [
    AppServiceProvider::class,
    PortalServiceProvider::class,
    HealthServiceProvider::class,
    
    // Auth module (admin panel + sessions)
    AuthServiceProvider::class,
    
    // Module providers
    CatalogServiceProvider::class,
    LocationsServiceProvider::class,
    PricingServiceProvider::class,
    StockServiceProvider::class,
    TaxonomyServiceProvider::class,
    UomServiceProvider::class,
];
