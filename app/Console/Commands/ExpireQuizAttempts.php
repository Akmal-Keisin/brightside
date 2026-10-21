<?php

namespace App\Console\Commands;

use App\Enums\QuizAttemptStatus;
use App\Models\QuizAttempt;
use Illuminate\Console\Command;

class ExpireQuizAttempts extends Command
{
    protected $signature = 'quiz:expire-attempts';

    protected $description = 'Expire timed quiz attempts that passed their deadline and grace period';

    public function handle(): int
    {
        $grace = (int) config('brightside.quiz_grace_seconds', 30);

        $count = QuizAttempt::query()
            ->where('status', QuizAttemptStatus::InProgress)
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<=', now()->subSeconds($grace))
            ->update([
                'status' => QuizAttemptStatus::Expired,
                'completed_at' => now(),
            ]);

        $this->info("Expired {$count} quiz attempt(s).");

        return self::SUCCESS;
    }
}
