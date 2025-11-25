<?php

namespace App\Stock\Domain\Entities;

final class Stock
{
	private string $sku;
	private string $locationId;
	private ?string $locationType;
	private int $quantity;

	public function __construct(string $sku, string $locationId, ?string $locationType, int $quantity)
	{
		$this->sku = $sku;
		$this->locationId = $locationId;
		$this->locationType = $locationType;
		$this->quantity = $quantity;
	}

	public function sku(): string
	{
		return $this->sku;
	}

	public function locationId(): string
	{
		return $this->locationId;
	}

	public function locationType(): ?string
	{
		return $this->locationType;
	}

	public function quantity(): int
	{
		return $this->quantity;
	}

	public function toArray(): array
	{
		return [
			'sku' => $this->sku,
			'location_id' => $this->locationId,
			'location_type' => $this->locationType,
			'quantity' => $this->quantity,
		];
	}
}

