<?php

namespace Thaumware\Portal\Contracts;

/**
 * Data fetcher interface
 */
interface DataFetcher
{
    public function fetch(array $origin, array $filters): array;
}
