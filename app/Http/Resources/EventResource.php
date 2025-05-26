<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'formatted_date' => $this->formatted_date,
            'duration' => $this->duration,
            'status' => $this->status,
            'available_spots' => $this->availableSpots(),
            'event_type' => new EventTypeResource($this->whenLoaded('eventType')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'cover_image' => new ImageResource($this->whenLoaded('coverImage')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'user' => new UserResource($this->whenLoaded('user')),
            'reservations_count' => $this->when(isset($this->reservations_count), $this->reservations_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_new' => $this->wasRecentlyCreated,
        ];
    }
}

