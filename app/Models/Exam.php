<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'year',
        'status',
        'duration_minutes',
    ];

    protected $casts = [
        'year' => 'integer',
        'duration_minutes' => 'integer',
    ];

    /**
     * Tự động tạo slug khi set title
     */
    protected static function booted(): void
    {
        static::creating(function (Exam $exam) {
            if (empty($exam->slug)) {
                $exam->slug = Str::slug($exam->title);
            }
        });
    }

    /**
     * Các nhóm câu hỏi của đề thi
     */
    public function questionGroups(): HasMany
    {
        return $this->hasMany(QuestionGroup::class);
    }

    /**
     * Các lần làm bài của đề thi
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /**
     * Scope: chỉ lấy đề đã publish
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: chỉ lấy đề draft
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Lấy route key name dùng slug thay vì id
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function publishedListCacheKey(): string
    {
        return 'exams_published';
    }

    public function questionGroupsCacheKey(): string
    {
        return "exam_question_groups_{$this->id}";
    }

    public function detailCacheKey(): string
    {
        return "exam_detail_{$this->id}";
    }

    public function totalQuestionsCacheKey(): string
    {
        return "exam_total_questions_{$this->id}";
    }

    public static function clearCacheById(int $examId): void
    {
        Cache::forget("exam_question_groups_{$examId}");
        Cache::forget("exam_total_questions_{$examId}");
        Cache::forget("exam_detail_{$examId}");
    }

    public static function clearPublishedListCache(): void
    {
        Cache::forget(self::publishedListCacheKey());
    }
}
