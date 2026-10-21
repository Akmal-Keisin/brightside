<?php

use App\Enums\QuizAttemptStatus;
use App\Models\QuizAttempt;

test('quiz attempts past the deadline and grace period are expired', function () {
    $overdue = QuizAttempt::factory()->create([
        'status' => QuizAttemptStatus::InProgress,
        'deadline_at' => now()->subMinutes(2),
    ]);

    $insideGrace = QuizAttempt::factory()->create([
        'status' => QuizAttemptStatus::InProgress,
        'deadline_at' => now()->subSeconds(5),
    ]);

    $future = QuizAttempt::factory()->create([
        'status' => QuizAttemptStatus::InProgress,
        'deadline_at' => now()->addMinutes(10),
    ]);

    $this->artisan('quiz:expire-attempts')->assertSuccessful();

    expect($overdue->fresh()->status)->toBe(QuizAttemptStatus::Expired)
        ->and($overdue->fresh()->completed_at)->not->toBeNull()
        ->and($insideGrace->fresh()->status)->toBe(QuizAttemptStatus::InProgress)
        ->and($future->fresh()->status)->toBe(QuizAttemptStatus::InProgress);
});
