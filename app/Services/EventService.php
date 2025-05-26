<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class EventService
{
    protected ImageService $imageService;

    /**
     * Create a new service instance.
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Get a paginated list of events with optional filters.
     */
    public function list(array $filters = [])
    {
        $query = Event::query()
            ->with(['eventType', 'location', 'coverImage'])
            ->withCount('reservations');

        // Apply filters
        if (isset($filters['event_type_id'])) {
            $query->ofType($filters['event_type_id']);
        }

        if (isset($filters['location_id'])) {
            $query->atLocation($filters['location_id']);
        }

        if (isset($filters['status'])) {
            switch ($filters['status']) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'past':
                    $query->past();
                    break;
                case 'ongoing':
                    $query->where('starts_at', '<=', now())
                        ->where('ends_at', '>=', now());
                    break;
            }
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('location', function (Builder $query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('eventType', function (Builder $query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort results
        $sortBy = $filters['sort_by'] ?? 'starts_at';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($filters['per_page'] ?? 10);
    }

    /**
     * Get a single event with its relationships.
     */
    public function show(Event $event)
    {
        return $event->load([
            'eventType',
            'location',
            'images',
            'coverImage',
            'user'
        ])->loadCount('reservations');
    }

    /**
     * Create a new event.
     */
    public function create(Request $request): Event
    {
        $event = DB::transaction(function () use ($request) {
            $user = Auth::user();
            
            // 
            if (!$user->hasAnyRole(['organizer','admin'])) {
                $user->assignRole('organizer');
            }

            $event = Event::create(array_merge(
                $request->validated(),
                ['user_id' => $user->id]
            ));

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $this->imageService->attachImage($event, $request);
            }

            return $event;
        });

        return $this->show($event);
    }

    /**
     * Update an existing event.
     */
    public function update(Event $event, Request $request): Event
    {
        $originalEvent = clone $event;

        DB::transaction(function () use ($event, $request, $originalEvent) {
            $event->update($request->validated());

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $this->imageService->attachImage($event, $request);
            }

            // Check if dates changed and handle any necessary updates to reservations
            if ($event->isDirty(['starts_at', 'ends_at'])) {
                // Logic to handle reservation updates if needed
            }
        });

        return $this->show($event);
    }

    /**
     * Delete an event and its related resources.
     */
    public function delete(Event $event): void
    {
        DB::transaction(function () use ($event) {
            // Delete related images
            $this->imageService->deleteImage($event->images);

            // Delete the event
            $event->delete();
        });
    }

    /**
     * Set a cover image for the event.
     */
    public function setCoverImage(Event $event, int $imageId)
    {
        return $this->imageService->setCoverImage($event, $imageId);
    }

    /**
     * Get upcoming events.
     */
    public function getUpcomingEvents(int $limit = 5)
    {
        return Event::upcoming()
            ->with(['eventType', 'location', 'coverImage'])
            ->withCount('reservations')
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get events by type.
     */
    public function getEventsByType(int $typeId, int $limit = 10)
    {
        return Event::ofType($typeId)
            ->with(['location', 'coverImage'])
            ->withCount('reservations')
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get events by location.
     */
    public function getEventsByLocation(int $locationId, int $limit = 10)
    {
        return Event::atLocation($locationId)
            ->with(['eventType', 'coverImage'])
            ->withCount('reservations')
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }
}



