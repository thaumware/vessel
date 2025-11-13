<?php

use App\AppServiceProvider;
use App\Shared\Providers\PortalServiceProvider;
use App\Locations\Infrastructure\LocationsServiceProvider;

return [
    AppServiceProvider::class,
    PortalServiceProvider::class,
    LocationsServiceProvider::class,
];
