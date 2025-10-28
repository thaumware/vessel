<?php

namespace App\Items\Domain\Entities;

class Batch
{
    private string $id;
    private string $batch_number;
    private string $expiration_date;
    private string $additional_info;

    public function __construct(
        string $id,
        string $batch_number,
        string $expiration_date,
        string $additional_info
    ) {
        $this->id = $id;
        $this->batch_number = $batch_number;
        $this->expiration_date = $expiration_date;
        $this->additional_info = $additional_info;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBatchNumber(): string
    {
        return $this->batch_number;
    }

    public function getExpirationDate(): string
    {
        return $this->expiration_date;
    }

    public function getAdditionalInfo(): string
    {
        return $this->additional_info;
    }
}