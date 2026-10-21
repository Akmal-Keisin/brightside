<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire';

    protected $description = 'Mark pending orders whose payment window has closed as expired';

    public function handle(): int
    {
        $count = Order::query()
            ->pending()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => OrderStatus::Expired]);

        $this->info("Expired {$count} pending order(s).");

        return self::SUCCESS;
    }
}
