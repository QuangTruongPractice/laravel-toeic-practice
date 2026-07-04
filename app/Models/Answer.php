<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'label',
        'content',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Câu hỏi chứa đáp án này
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
