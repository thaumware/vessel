<?php

namespace App\Stock\Domain\Entities;

final class Batch
{
	private string $id;
	private string $sku;
	private string $location_id;
	private int $quantity;
	private ?string $lot_number;

	public function __construct(string $id, string $sku, string $location_id, int $quantity, ?string $lot_number = null)
	{
		$this->id = $id;
		$this->sku = $sku;
		$this->location_id = $location_id;
		$this->quantity = $quantity;
		$this->lot_number = $lot_number;
	}

	public function id(): string
	{
		return $this->id;
	}

	public function sku(): string
	{
		return $this->sku;
	}

	public function locationId(): string
	{
		return $this->location_id;
	}

	public function quantity(): int
	{
		return $this->quantity;
	}

	public function lotNumber(): ?string
	{
		return $this->lot_number;
	}
}

