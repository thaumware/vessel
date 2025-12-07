<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Entities\Unit;
use App\Stock\Domain\Interfaces\UnitRepositoryInterface;

final class CreateUnit
{
    public function __construct(private UnitRepositoryInterface $units)
    {
    }

    public function execute(string $id, string $code, string $name): Unit
    {
        $unit = new Unit($id, $code, $name);
        return $this->units->save($unit);
    }
}
