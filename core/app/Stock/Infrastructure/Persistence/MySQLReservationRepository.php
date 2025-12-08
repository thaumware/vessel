<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\Persistence;

use App\Stock\Domain\Reservation;
use App\Stock\Domain\ReservationRepository;
use App\Stock\Domain\ReservationStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class MySQLReservationRepository implements ReservationRepository
{
    public function save(Reservation $reservation): void
    {
        DB::table('stock_reservations')->upsert(
            [
                'id' => $reservation->getId(),
                'item_id' => $reservation->getItemId(),
                'location_id' => $reservation->getLocationId(),
                'quantity' => $reservation->getQuantity(),
                'reserved_by' => $reservation->getReservedBy(),
                'reference_type' => $reservation->getReferenceType(),
                'reference_id' => $reservation->getReferenceId(),
                'status' => $reservation->getStatus()->value,
                'expires_at' => $reservation->getExpiresAt()?->format('Y-m-d H:i:s'),
                'created_at' => $reservation->getCreatedAt()?->format('Y-m-d H:i:s'),
                'released_at' => $reservation->getReleasedAt()?->format('Y-m-d H:i:s'),
            ],
            ['id'],
            ['quantity', 'status', 'released_at']
        );
    }

    public function findById(string $id): ?Reservation
    {
        $row = DB::table('stock_reservations')->where('id', $id)->first();

        if ($row === null) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findActiveByItemAndLocation(string $itemId, string $locationId): array
    {
        $rows = DB::table('stock_reservations')
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('status', ReservationStatus::ACTIVE->value)
            ->get();

        return array_map([$this, 'hydrate'], $rows->all());
    }

    public function findActive(): array
    {
        $rows = DB::table('stock_reservations')
            ->where('status', ReservationStatus::ACTIVE->value)
            ->orderBy('created_at', 'desc')
            ->get();

        return array_map([$this, 'hydrate'], $rows->all());
    }

    public function findByReference(string $referenceType, string $referenceId): array
    {
        $rows = DB::table('stock_reservations')
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get();

        return array_map([$this, 'hydrate'], $rows->all());
    }

    public function markExpired(): int
    {
        return DB::table('stock_reservations')
            ->where('status', ReservationStatus::ACTIVE->value)
            ->where('expires_at', '<', now())
            ->update([
                'status' => ReservationStatus::EXPIRED->value,
                'released_at' => now(),
            ]);
    }

    public function delete(string $id): void
    {
        DB::table('stock_reservations')->where('id', $id)->delete();
    }

    private function hydrate(object $row): Reservation
    {
        return new Reservation(
            id: $row->id,
            itemId: $row->item_id,
            locationId: $row->location_id,
            quantity: (float) $row->quantity,
            reservedBy: $row->reserved_by,
            referenceType: $row->reference_type,
            referenceId: $row->reference_id,
            status: ReservationStatus::from($row->status),
            expiresAt: $row->expires_at ? new DateTimeImmutable($row->expires_at) : null,
            createdAt: $row->created_at ? new DateTimeImmutable($row->created_at) : null,
            releasedAt: $row->released_at ? new DateTimeImmutable($row->released_at) : null,
        );
    }
}
