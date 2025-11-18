<?php

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\Unit;

interface UnitRepositoryInterface
{
    public function save(Unit $unit): Unit;

    public function findById(string $id): ?Unit;

    public function findByCode(string $code): ?Unit;
}