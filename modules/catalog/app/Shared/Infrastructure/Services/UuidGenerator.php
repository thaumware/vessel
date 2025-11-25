<?php

namespace App\Shared\Infrastructure\Services;

use App\Shared\Domain\Interfaces\IdGeneratorInterface;
use Illuminate\Support\Str;

/**
 * UuidGenerator - Implementación de IdGeneratorInterface usando Laravel Str::uuid().
 */
class UuidGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        return Str::uuid()->toString();
    }
}
