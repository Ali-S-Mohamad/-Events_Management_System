<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'guests_count' => $this->guests_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'event' => $this->whenLoaded('event', function() {
                return [
                    'id' => $this->event->id,
                    'title' => $this->event->title,
                    'starts_at' => $this->event->starts_at,
                    'ends_at' => $this->event->ends_at,
                    'location' => $this->whenLoaded('event.location', function() {
                        return [
                            'id' => $this->event->location->id,
                            'name' => $this->event->location->name,
                        ];
                    }),
                ];
            }),
            'user' => $this->whenLoaded('user', function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'is_new' => $this->when($this->wasRecentlyCreated, true),
        ];
    }
}
