<?php

namespace App\Uom\Domain\Interfaces;

interface MeasureRepository
{
    public function findById(string $id);
    public function findAll(): array;

    public function save($measure): void;
}