<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    // Table name (optional if following Laravel naming conventions)
    protected $table = 'quiz_attempts';

    // Fillable fields - what can be mass assigned
    protected $fillable = [
        'user_id',
        'user_name',
        'unique_id',
        'quiz_unique_id',
        'lesson_id',
        'course_id',
        'course_video_id', // Added field for course video ID
        'score',
        'completed',
        'quiz_expiry',
        'attempted_at',
        'quiz_started_at',
        'quiz_ended_at'
    ];

    // Cast certain fields to specific types
    protected $casts = [
        'completed' => 'boolean',
        'attempted_at' => 'datetime',
        'score' => 'float',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Scope to check if a user has attempted a specific lesson's quiz
    public function scopeHasAttempted($query, $userId, $lessonId)
    {
        return $query->where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->exists();
    }

    // Optionally, you can add a method to get the quiz video URL or quiz file
    public function getQuizFile()
    {
        // You can modify this to return the appropriate quiz file
        return $this->lesson->video ? json_decode($this->lesson->video, true) : null;
    }
}
