<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('app:create-admin {email? : The admin email address} {--name= : Display name} {--password= : Password (prompted when omitted)}')]
#[Description('Create or promote a Brightside administrator')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = strtolower(trim((string) ($this->argument('email') ?: $this->ask('Admin email address'))));

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email', 'max:190'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();
        $password = $this->option('password');

        if ($password === null && $user === null) {
            $password = $this->secret('Password (min 10 characters)');
        }

        if ($password !== null) {
            $passwordValidator = Validator::make(['password' => $password], [
                'password' => ['required', 'string', Password::defaults()],
            ]);

            if ($passwordValidator->fails()) {
                foreach ($passwordValidator->errors()->all() as $error) {
                    $this->components->error($error);
                }

                return self::FAILURE;
            }
        }

        $attributes = [
            'name' => $this->option('name') ?: $user?->name ?: 'Brightside Admin',
            'role' => UserRole::Admin,
            'email_verified_at' => $user?->email_verified_at ?? now(),
            'terms_accepted_at' => $user?->terms_accepted_at ?? now(),
            'terms_version' => $user?->terms_version ?? (string) config('brightside.terms_version', '1.0'),
        ];

        if ($password !== null) {
            $attributes['password'] = $password;
        }

        if ($user !== null) {
            $user->forceFill($attributes)->save();
            $this->components->info("Promoted {$user->email} to administrator.");
        } else {
            $user = (new User)->forceFill([
                'email' => $email,
                'password' => $password,
                ...$attributes,
            ]);
            $user->save();
            $this->components->info("Created administrator {$user->email}.");
        }

        if ($user->two_factor_confirmed_at === null) {
            $this->components->warn('Two-factor authentication is required for studio access. Enable it under Settings → Security after logging in.');
        }

        return self::SUCCESS;
    }
}
