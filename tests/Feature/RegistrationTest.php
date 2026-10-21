<?php

use App\Enums\UserRole;
use App\Models\User;

test('registration screen can be rendered', function () {
    $this->get(route('register'))->assertOk();
});

test('new members can create an account', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Rani Putri',
        'email' => 'rani@example.com',
        'password' => 'Brightside123',
        'password_confirmation' => 'Brightside123',
        'terms' => 'on',
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'rani@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::Member)
        ->and($user->terms_accepted_at)->not->toBeNull()
        ->and($user->email_verified_at)->toBeNull();
});

test('a member must accept the terms to register', function () {
    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'Rani Putri',
        'email' => 'rani@example.com',
        'password' => 'Brightside123',
        'password_confirmation' => 'Brightside123',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors('terms');
    $this->assertGuest();
});

test('registration requires a strong password', function () {
    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'Rani Putri',
        'email' => 'rani@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
        'terms' => 'on',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('an unverified member is sent to the verification notice', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
});

test('registration returns to the intended url', function () {
    $this->get(route('register', ['redirect' => '/courses']));

    $this->post(route('register.store'), [
        'name' => 'Rani Putri',
        'email' => 'rani@example.com',
        'password' => 'Brightside123',
        'password_confirmation' => 'Brightside123',
        'terms' => 'on',
    ])->assertRedirect('/courses');
});
