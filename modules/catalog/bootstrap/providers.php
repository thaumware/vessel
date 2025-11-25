<?php

use App\AppServiceProvider;
use App\Shared\Providers\PortalServiceProvider;
use App\Items\Infrastructure\ItemsServiceProvider;
use App\Locations\Infrastructure\LocationsServiceProvider;
use App\Pricing\Infrastructure\PricingServiceProvider;
use App\Stock\Infrastructure\StockServiceProvider;
use App\Taxonomy\Infrastructure\TaxonomyServiceProvider;
use App\Uom\Infrastructure\UomServiceProvider;

return [
    AppServiceProvider::class,
    PortalServiceProvider::class,
    
    // Module providers
    ItemsServiceProvider::class,
    LocationsServiceProvider::class,
    PricingServiceProvider::class,
    StockServiceProvider::class,
    TaxonomyServiceProvider::class,
    UomServiceProvider::class,
];
