<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'exam_title' => $this->exam->title ?? null,
            'mode' => $this->mode,
            'status' => $this->status,
            'listening_score' => $this->listening_score,
            'reading_score' => $this->reading_score,
            'total_score' => $this->total_score,
            'total_correct' => $this->total_correct,
            'total_questions' => $this->total_questions,
            'time_spent_seconds' => $this->time_spent_seconds,
            'accuracy_percent' => $this->accuracy_percent,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
