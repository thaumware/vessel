<?php

namespace App\Stock\Domain\Entities;

final class Batch
{
	private string $id;
	private string $sku;
	private string $locationId;
	private int $quantity;
	private ?string $lotNumber;

	public function __construct(string $id, string $sku, string $locationId, int $quantity, ?string $lotNumber = null)
	{
		$this->id = $id;
		$this->sku = $sku;
		$this->locationId = $locationId;
		$this->quantity = $quantity;
		$this->lotNumber = $lotNumber;
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
		return $this->locationId;
	}

	public function quantity(): int
	{
		return $this->quantity;
	}

	public function lotNumber(): ?string
	{
		return $this->lotNumber;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'sku' => $this->sku,
			'location_id' => $this->locationId,
			'quantity' => $this->quantity,
			'lot_number' => $this->lotNumber,
		];
	}
}

