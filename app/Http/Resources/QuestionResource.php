<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_number' => $this->question_number,
            'content' => $this->content,
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}
