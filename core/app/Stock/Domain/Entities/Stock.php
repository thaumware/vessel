<?php

namespace App\Stock\Domain\Entities;

final class Stock
{
	private string $itemId;
	private string $locationId;
	private ?string $locationType;
	private int $quantity;

	public function __construct(string $itemId, string $locationId, ?string $locationType, int $quantity)
	{
		$this->itemId = $itemId;
		$this->locationId = $locationId;
		$this->locationType = $locationType;
		$this->quantity = $quantity;
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
			'item_id' => $this->itemId,
			'sku' => $this->itemId, // alias legado
			'location_id' => $this->locationId,
			'location_type' => $this->locationType,
			'quantity' => $this->quantity,
		];
	}
}

