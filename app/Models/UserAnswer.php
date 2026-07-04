<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_answer_id',
        'is_correct',
        'created_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Lần làm bài chứa câu trả lời này
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    /**
     * Câu hỏi được trả lời
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Đáp án đã chọn
     */
    public function selectedAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'selected_answer_id');
    }
}
