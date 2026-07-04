<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'mode',
        'listening_score',
        'reading_score',
        'total_score',
        'total_correct',
        'total_questions',
        'time_spent_seconds',
        'parts_attempted',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'listening_score' => 'integer',
        'reading_score' => 'integer',
        'total_score' => 'integer',
        'total_correct' => 'integer',
        'total_questions' => 'integer',
        'time_spent_seconds' => 'integer',
        'parts_attempted' => 'array',   // JSON -> PHP array tự động
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * User đã làm bài
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Đề thi được làm
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Các câu trả lời trong lần làm bài
     */
    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'attempt_id');
    }

    /**
     * Kiểm tra bài thi đã hoàn thành chưa
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Kiểm tra đang làm bài
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function getRemainingTimeSeconds(int $durationSeconds, ?CarbonInterface $now = null): int
    {
        if ($this->status !== 'in_progress' || !$this->started_at) {
            return $durationSeconds;
        }

        $referenceTime = $now ?? now();
        $elapsedSeconds = max(0, $referenceTime->diffInSeconds($this->started_at));

        return max(0, $durationSeconds - $elapsedSeconds);
    }

    /**
     * Tính phần trăm đúng
     */
    public function getAccuracyPercentAttribute(): float
    {
        if ($this->total_questions === 0) {
            return 0;
        }

        return round(($this->total_correct / $this->total_questions) * 100, 1);
    }

    /**
     * Format thời gian làm bài
     */
    public function getFormattedTimeAttribute(): string
    {
        $minutes = intdiv($this->time_spent_seconds, 60);
        $seconds = $this->time_spent_seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
