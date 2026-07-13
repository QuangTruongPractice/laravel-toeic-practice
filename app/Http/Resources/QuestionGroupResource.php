<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'part_id' => $this->part_id,
            'part_number' => $this->part->part_number ?? null,
            'part_name' => $this->part->name ?? null,
            'passage' => $this->passage,
            'audio_url' => $this->audio_url,
            'image_url' => $this->image_url,
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
