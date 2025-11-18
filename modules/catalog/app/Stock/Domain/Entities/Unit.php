<?php

namespace App\Stock\Domain\Entities;

final class Unit
{
	private string $id;
	private string $code;
	private string $name;

	public function __construct(string $id, string $code, string $name)
	{
		$this->id = $id;
		$this->code = $code;
		$this->name = $name;
	}

	public function id(): string
	{
		return $this->id;
	}

	public function code(): string
	{
		return $this->code;
	}

	public function name(): string
	{
		return $this->name;
	}
}

