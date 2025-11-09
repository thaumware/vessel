<?php

namespace Thaumware\Portal;

/**
 * Base service provider
 * 
 * Laravel apps should extend this and bind adapters
 */
abstract class PortalServiceProvider
{
    abstract public function register(): void;
    
    abstract public function boot(): void;
}
