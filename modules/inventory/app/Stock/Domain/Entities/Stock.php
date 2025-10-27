<?php

namespace App\Stock\Domain\Entities;

class Stock
{
    private string $stockable_id;
    private string $stockable_type;


    public static array $VALID_STOCKABLE_TYPES = [
        'item',
        'stock_unidad'
    ];

    public function __construct(string $stockable_id, string $stockable_type, float $cantidad, string $locacion_id)
    {
        $this->stockable_id = $stockable_id;
        $this->stockable_type = $stockable_type;
    }

    public function getStockableId(): string
    {
        return $this->stockable_id;
    }

    public function getStockableType(): string
    {
        return $this->stockable_type;
    }

}