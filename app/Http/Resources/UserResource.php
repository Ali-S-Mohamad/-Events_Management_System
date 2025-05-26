<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->whenLoaded('roles', function() {
                return $this->roles->pluck('name');
            }),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
            'reservations_count' => $this->when(isset($this->reservations_count), $this->reservations_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}