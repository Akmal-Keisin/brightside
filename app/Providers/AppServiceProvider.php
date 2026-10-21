<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Models\User;
use App\Services\Payments\FakePaymentGateway;
use App\Services\Payments\MidtransGateway;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function (): PaymentGateway {
            return filled(config('brightside.midtrans.server_key'))
                ? new MidtransGateway
                : new FakePaymentGateway;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): Password => Password::min(10)
            ->mixedCase()
            ->numbers(),
        );

        Gate::define('access-admin', fn (User $user): bool => $user->isAdmin());

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });
    }
}
