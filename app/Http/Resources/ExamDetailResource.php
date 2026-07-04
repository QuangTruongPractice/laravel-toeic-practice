<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'year' => $this->year,
            'duration_minutes' => $this->duration_minutes,
            'question_groups' => QuestionGroupResource::collection($this->whenLoaded('questionGroups')),
        ];
    }
}
