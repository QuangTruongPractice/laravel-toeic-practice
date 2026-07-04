<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    public $timestamps = false; // Parts là data cố định, không cần timestamps

    protected $fillable = [
        'part_number',
        'name',
        'section',
        'description',
        'directions',
        'question_count',
    ];

    /**
     * Các nhóm câu hỏi thuộc Part này
     */
    public function questionGroups(): HasMany
    {
        return $this->hasMany(QuestionGroup::class);
    }

    /**
     * Kiểm tra Part thuộc Listening section
     */
    public function isListening(): bool
    {
        return $this->section === 'listening';
    }

    /**
     * Kiểm tra Part thuộc Reading section
     */
    public function isReading(): bool
    {
        return $this->section === 'reading';
    }
}
