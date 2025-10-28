<?php

namespace App\Shared\Domain\Traits;

use Illuminate\Support\Str;


trait HasId
{

    private string $id;

    public function getId(): string
    {
        return $this->id;
    }

    public function generateId(): void
    {
        $this->id = Str::uuid()->toString();
    }

    public function setId(?string $id = null, bool $generateIfNull = false): void
    {
        if ($generateIfNull && $id === null) {
            $this->generateId();
            return;
        }
        $this->id = $id;
    }
}

