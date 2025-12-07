<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

// Example broadcast channels for the Stock module.
// Protect/adjust as needed when adding real-time features.
Broadcast::channel('stock.notifications', fn () => true);
