<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $input['email'] = Str::lower(trim((string) ($input['email'] ?? '')));
        $input['name'] = trim((string) ($input['name'] ?? ''));

        Validator::make($input, $this->rules())->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'terms_accepted_at' => now(),
            'terms_version' => (string) config('brightside.terms_version', '1.0'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique(User::class)],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'terms' => ['required', 'accepted'],
        ];
    }
}
