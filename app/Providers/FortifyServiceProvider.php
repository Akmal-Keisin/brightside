<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(function (Request $request) {
            $this->rememberIntendedUrl($request);

            return Inertia::render('auth/Login', [
                'canResetPassword' => Features::enabled(Features::resetPasswords()),
                'canRegister' => Features::enabled(Features::registration()),
                'status' => $request->session()->get('status'),
            ]);
        });

        Fortify::registerView(function (Request $request) {
            $this->rememberIntendedUrl($request);

            return Inertia::render('auth/Register', [
                'passwordRules' => Password::defaults()->toPasswordRulesString(),
            ]);
        });

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));
    }

    private function rememberIntendedUrl(Request $request): void
    {
        $redirect = $request->query('redirect');

        if (is_string($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            $request->session()->put('url.intended', $redirect);
        }
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

    }
}
