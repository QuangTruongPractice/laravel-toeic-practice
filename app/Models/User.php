<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Các lần làm bài thi của user
     */
    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /**
     * Các lần import đề thi (chỉ admin)
     */
    public function imports(): HasMany
    {
        return $this->hasMany(Import::class, 'admin_id');
    }

    /**
     * Kiểm tra user có phải admin không
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Tính điểm trung bình của user
     */
    public function getAverageScoreAttribute(): int
    {
        return (int) $this->examAttempts()->where('status', 'completed')->avg('total_score');
    }
}

