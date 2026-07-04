<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class QuestionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'part_id',
        'passage',
        'audio_path',
        'image_path',
        'order_number',
    ];

    protected $casts = [
        'order_number' => 'integer',
    ];

    /**
     * Đề thi chứa nhóm câu hỏi này
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Part mà nhóm câu hỏi thuộc về
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Các câu hỏi trong nhóm
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order_in_group');
    }

    /**
     * Các đáp án của tất cả câu hỏi trong nhóm (through relationship)
     */
    public function answers(): HasManyThrough
    {
        return $this->hasManyThrough(Answer::class, Question::class);
    }

    /**
     * Kiểm tra nhóm có audio không
     */
    public function hasAudio(): bool
    {
        return !empty($this->audio_path);
    }

    /**
     * Kiểm tra nhóm có hình ảnh không
     */
    public function hasImage(): bool
    {
        return !empty($this->image_path);
    }
}
