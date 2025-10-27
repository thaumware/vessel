<?php

namespace App\Stock\Domain\Entities;

class Lote
{
    private string $lote_id;
    private string $item_id;
    private string $item_type;
    private float $cantidad;
    private string $uom_id;

    public function __construct(
        string $lote_id,
        string $item_id,
        string $item_type,
        float $cantidad,
        string $uom_id
    ) {
        $this->lote_id = $lote_id;
        $this->item_id = $item_id;
        $this->item_type = $item_type;
        $this->cantidad = $cantidad;
        $this->uom_id = $uom_id;
    }

    public function getLoteId(): string
    {
        return $this->lote_id;
    }

    public function getItemId(): string
    {
        return $this->item_id;
    }
    public function getItemType(): string
    {
        return $this->item_type;
    }

    public function getCantidad(): float
    {
        return $this->cantidad;
    }

    public function getUomId(): string
    {
        return $this->uom_id;
    }



}