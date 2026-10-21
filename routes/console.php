<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('orders:expire')->everyTenMinutes()->withoutOverlapping();
Schedule::command('payments:reconcile')->everyTenMinutes()->withoutOverlapping();
Schedule::command('quiz:expire-attempts')->everyMinute()->withoutOverlapping();
