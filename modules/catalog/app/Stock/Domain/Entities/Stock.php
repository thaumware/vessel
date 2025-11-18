<?php

namespace App\Stock\Domain\Entities;

final class Stock
{
	private string $sku;
	private string $location_id;
	private ?string $location_type;
	private int $quantity;

	public function __construct(string $sku, string $location_id, ?string $location_type, int $quantity)
	{
		$this->sku = $sku;
		$this->location_id = $location_id;
		$this->location_type = $location_type;
		$this->quantity = $quantity;
	}

	public function sku(): string
	{
		return $this->sku;
	}

	public function locationId(): string
	{
		return $this->location_id;
	}

	public function locationType(): ?string
	{
		return $this->location_type;
	}

	public function quantity(): int
	{
		return $this->quantity;
	}
}

