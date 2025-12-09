<?php

declare(strict_types=1);

namespace App\Stock\Application\Commands;

use App\Stock\Domain\ReservationRepository;
use App\Stock\Application\UseCases\ReleaseReservation\ReleaseReservationUseCase;
use App\Stock\Application\UseCases\ReleaseReservation\ReleaseReservationRequest;
use Illuminate\Console\Command;

/**
 * Comando para marcar y liberar reservas expiradas.
 * 
 * Uso: php artisan stock:expire-reservations
 * 
 * Se recomienda ejecutar cada hora via cron:
 * 0 * * * * php artisan stock:expire-reservations
 */
class ExpireReservationsCommand extends Command
{
    protected $signature = 'stock:expire-reservations 
                            {--dry-run : Show what would be expired without making changes}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Mark and release expired stock reservations';

    public function __construct(
        private ReservationRepository $reservationRepository,
        private ReleaseReservationUseCase $releaseReservation
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔍 Searching for expired reservations...');

        // Obtener reservas activas con fecha de expiración pasada
        $allActive = $this->reservationRepository->findActive();
        $expired = array_filter($allActive, fn($r) => $r->isExpired());

        if (empty($expired)) {
            $this->info('✅ No expired reservations found.');
            return Command::SUCCESS;
        }

        $count = count($expired);
        $this->warn("⚠️  Found {$count} expired reservation(s).");

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Item', 'Location', 'Quantity', 'Expired At'],
                array_map(fn($r) => [
                    $r->getId(),
                    $r->getItemId(),
                    $r->getLocationId(),
                    $r->getQuantity(),
                    $r->getExpiresAt()?->format('Y-m-d H:i:s'),
                ], $expired)
            );
            $this->info('🧪 Dry-run mode: No changes made.');
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Do you want to expire these reservations?')) {
            $this->info('Cancelled by user.');
            return Command::FAILURE;
        }

        $released = 0;
        $errors = 0;

        $this->output->progressStart($count);

        foreach ($expired as $reservation) {
            try {
                // Marcar como expirada en la tabla
                $expired = $reservation->expire();
                $this->reservationRepository->save($expired);

                // Liberar el stock reservado
                $result = $this->releaseReservation->execute(
                    new ReleaseReservationRequest(
                        itemId: $reservation->getItemId(),
                        locationId: $reservation->getLocationId(),
                        quantity: $reservation->getQuantity(),
                        referenceType: 'reservation_expiration',
                        referenceId: $reservation->getId(),
                        reason: 'Reservation expired automatically',
                        reservationId: $reservation->getId()
                    )
                );

                if ($result->success) {
                    $released++;
                } else {
                    $errors++;
                    $this->error("Failed to release {$reservation->getId()}: " . implode(', ', $result->errors));
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error processing {$reservation->getId()}: {$e->getMessage()}");
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->newLine();
        $this->info("✅ Successfully expired: {$released}");
        if ($errors > 0) {
            $this->error("❌ Errors: {$errors}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
