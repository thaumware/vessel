<?php

declare(strict_types=1);

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\Lot;
use DateTimeInterface;

/**
 * Interface para repositorio de Lotes.
 */
interface LotRepositoryInterface
{
    public function save(Lot $lot): Lot;

    public function findById(string $id): ?Lot;

    public function findByLotNumber(string $lotNumber): ?Lot;

    /**
     * @return Lot[]
     */
    public function findBySku(string $sku): array;

    /**
     * @return Lot[]
     */
    public function findBySkuAndLocation(string $sku, string $locationId): array;

    /**
     * Lotes que expiran antes de la fecha dada.
     * @return Lot[]
     */
    public function findExpiringBefore(DateTimeInterface $date): array;

    /**
     * Lotes vencidos.
     * @return Lot[]
     */
    public function findExpired(): array;

    /**
     * Lotes con stock disponible, ordenados por fecha de vencimiento (FEFO).
     * @return Lot[]
     */
    public function findAvailableBySku(string $sku): array;

    public function delete(string $id): bool;

    /**
     * @return Lot[]
     */
    public function all(): array;
}
