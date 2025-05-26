<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_users' => $this->collection->count(),
                'has_admin' => $this->collection->contains(function ($user) {
                    return $user->hasRole('admin');
                }),
            ],
            'links' => [
                'self' => url('/api/v1/users'),
            ],
        ];
    }
}