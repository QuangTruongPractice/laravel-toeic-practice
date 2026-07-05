<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Question extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'question_group_id',
        'content',
        'question_number',
        'order_in_group',
        'explanation',
    ];

    protected $casts = [
        'question_number' => 'integer',
        'order_in_group' => 'integer',
    ];

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'question_number' => $this->question_number,
            'explanation' => $this->explanation,
        ];
    }

    /**
     * Nhóm câu hỏi chứa câu hỏi này
     */
    public function questionGroup(): BelongsTo
    {
        return $this->belongsTo(QuestionGroup::class);
    }

    /**
     * Các đáp án của câu hỏi
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('label');
    }

    /**
     * Đáp án đúng
     */
    public function correctAnswer(): HasOne
    {
        return $this->hasOne(Answer::class)->where('is_correct', true);
    }

    /**
     * Các câu trả lời của user cho câu hỏi này
     */
    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    /**
     * Lấy Part thông qua QuestionGroup
     */
    public function getPart(): Part
    {
        return $this->questionGroup->part;
    }
}
