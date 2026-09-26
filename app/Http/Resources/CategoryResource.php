<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'color' => $this->color,
            'icon' => $this->icon,
            'tasks_count' => $this->whenCounted('tasks', $this->tasks_count, 0),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
