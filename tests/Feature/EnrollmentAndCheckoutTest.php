<?php

use App\Enums\EnrollmentSource;
use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Notifications\EnrollmentConfirmedNotification;
use Illuminate\Support\Facades\Notification;

test('a member can enroll in a free course', function () {
    $course = Course::factory()->free()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('courses.enroll', $course))
        ->assertRedirect(route('learning.resume', $course));

    $enrollment = Enrollment::query()
        ->where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->source)->toBe(EnrollmentSource::Free)
        ->and($enrollment->isActive())->toBeTrue();
});

test('free enrollment sends one confirmation notification', function () {
    Notification::fake();

    $course = Course::factory()->free()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('courses.enroll', $course))->assertRedirect();
    $this->actingAs($user)->post(route('courses.enroll', $course))->assertRedirect();

    Notification::assertSentOnDemandTimes(EnrollmentConfirmedNotification::class, 1);
});

test('a member cannot free-enroll in a paid course', function () {
    $course = Course::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('courses.enroll', $course))
        ->assertSessionHasErrors('course');

    expect(Enrollment::query()->count())->toBe(0);
});

test('checkout creates a pending order and redirects to the gateway', function () {
    $course = Course::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('checkout.store', $course));

    $order = Order::query()->firstOrFail();

    $response->assertRedirect(route('checkout.status', $order));

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->user_id)->toBe($user->id)
        ->and($order->total_minor)->toBe($course->price_minor)
        ->and($order->payment_provider)->toBe('fake')
        ->and($order->payment_url)->not->toBeNull();
});

test('checkout reuses an active pending order instead of creating duplicates', function () {
    $course = Course::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('checkout.store', $course))->assertRedirect();
    $this->actingAs($user)->post(route('checkout.store', $course))->assertRedirect();

    expect(Order::query()->count())->toBe(1)
        ->and(Order::query()->firstOrFail()->status)->toBe(OrderStatus::Pending);
});

test('a local simulated payment completes the order and grants access', function () {
    $course = Course::factory()->create();
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => OrderStatus::Pending,
    ]);

    $this->actingAs($user)
        ->post(route('dev.orders.pay', $order))
        ->assertRedirect(route('checkout.status', $order));

    expect($order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($order->fresh()->payments()->count())->toBe(1);

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $user->id,
        'course_id' => $course->id,
        'order_id' => $order->id,
        'source' => EnrollmentSource::Purchase->value,
    ]);
});

test('members cannot view another member order', function () {
    $order = Order::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->get(route('orders.show', $order))
        ->assertNotFound();
});

test('the checkout page requires authentication', function () {
    $course = Course::factory()->create();

    $this->get(route('checkout.show', $course))
        ->assertRedirect(route('login'));
});
