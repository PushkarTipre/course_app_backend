<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Log;

class CleanupExpiredQuizzes extends Command
{
    protected $signature = 'quiz:cleanup-expired';
    protected $description = 'Mark incomplete and expired quizzes as abandoned';

    public function handle()
    {
        Log::info('Cleaning up expired quizzes');

        // Fetch quizzes that have expired and are incomplete
        $expiredQuizzes = QuizAttempt::where('completed', false)
            ->where('quiz_expiry', '<=', now()->setTimezone('Asia/Kolkata'))
            ->update([
                'score' => 0.0,
                'completed' => true,

            ]);

        Log::info("Marked {$expiredQuizzes} expired quizzes as abandoned.");
        $this->info("Marked {$expiredQuizzes} expired quizzes as abandoned.");
    }
}
