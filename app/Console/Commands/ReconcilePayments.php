<?php

namespace App\Console\Commands;

use App\Services\ReconcilePendingPayments;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--minutes=10 : Only inspect orders older than this} {--limit=50 : Maximum orders per run}';

    protected $description = 'Ask the payment gateway for authoritative status of stale pending orders';

    public function handle(ReconcilePendingPayments $reconciler): int
    {
        $applied = $reconciler->handle(
            staleMinutes: (int) $this->option('minutes'),
            limit: (int) $this->option('limit'),
        );

        $this->info("Reconciled {$applied} order(s).");

        return self::SUCCESS;
    }
}
