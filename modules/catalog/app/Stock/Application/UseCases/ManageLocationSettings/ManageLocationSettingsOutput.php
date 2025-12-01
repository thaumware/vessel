<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ManageLocationSettings;

use App\Stock\Domain\Entities\LocationStockSettings;

class ManageLocationSettingsOutput
{
    public function __construct(
        public readonly bool $success,
        public readonly ?LocationStockSettings $settings = null,
        public readonly ?string $error = null,
    ) {}

    public static function success(LocationStockSettings $settings): self
    {
        return new self(true, $settings);
    }

    public static function error(string $message): self
    {
        return new self(false, null, $message);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'settings' => $this->settings?->toArray(),
            'error' => $this->error,
        ];
    }
}
