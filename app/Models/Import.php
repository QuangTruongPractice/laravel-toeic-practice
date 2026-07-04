<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Import extends Model
{
    protected $fillable = [
        'admin_id',
        'exam_id',
        'pdf_path',
        'audio_path',
        'status',
        'error_log',
        'questions_created',
    ];

    protected $casts = [
        'questions_created' => 'integer',
    ];

    /**
     * Admin đã upload
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Đề thi được tạo ra từ import này
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    /**
     * Kiểm tra import đã hoàn thành
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Kiểm tra import bị lỗi
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
