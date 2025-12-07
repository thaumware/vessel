<?php

namespace App\Stock\Domain\Entities;

final class Batch
{
	private string $id;
	private string $itemId;
	private string $locationId;
	private int $quantity;
	private ?string $lotNumber;

	public function __construct(string $id, string $itemId, string $locationId, int $quantity, ?string $lotNumber = null)
	{
		$this->id = $id;
		$this->itemId = $itemId;
		$this->locationId = $locationId;
		$this->quantity = $quantity;
		$this->lotNumber = $lotNumber;
	}

	public function id(): string
	{
		return $this->id;
	}

	public function itemId(): string
	{
		return $this->itemId;
	}

	/**
	 * @deprecated usar itemId(); alias legado
	 */
	public function sku(): string
	{
		return $this->itemId;
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
			'item_id' => $this->itemId,
			'sku' => $this->itemId,
			'location_id' => $this->locationId,
			'quantity' => $this->quantity,
			'lot_number' => $this->lotNumber,
		];
	}
}

