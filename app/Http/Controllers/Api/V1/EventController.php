<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Services\EventService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventCollection;
use App\Http\Requests\Event\StoreEventRequest;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Requests\Event\SetCoverImageRequest;

class EventController extends Controller
{
    protected EventService $eventService;

    /**
     * Create a new controller instance.
     */
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }
    /**
     * Summary of middleware
     * @return array<Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('role:admin|organizer', only:['store', 'update', 'destroy', 'setCoverImage'])
        ];
    }

    /**
     * Display a listing of the events
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'event_type_id',
            'location_id',
            'status',
            'search',
            'sort_by',
            'sort_direction',
            'per_page'
        ]);


        if (auth()->user()->hasRole('organizer') && !auth()->user()->hasRole('admin')) {
            $filters['user_id'] = auth()->id();
        }

        $events = $this->eventService->list($filters);
        
        return $this->apiResponse(
            new EventCollection($events), 
            'Events retrieved successfully', 
            200
        );
    }


    /**
     * Store a newly created event in storage.
     * @param \App\Http\Requests\Event\StoreEventRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEventRequest $request)
    {
        Gate::authorize('create', Event::class);
        
        $event = $this->eventService->create($request);
        
        if ($event->wasRecentlyCreated) {
            return $this->successResponse(
                new EventResource($event),
                'Event created successfully',
                201
            );
        }
        
        return $this->errorResponse('Failed to create event', 500);
    }


    /**
     * Display the specified event.
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Event $event)
    {
        Gate::authorize('view', $event);
        
        $event = $this->eventService->show($event);
        
        return $this->apiResponse(
            new EventResource($event),
            'Event retrieved successfully',
            200
        );
    }


    /**
     * Summary of update
     * @param \App\Http\Requests\Event\UpdateEventRequest $request
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        Gate::authorize('update', $event);
        
        $updatedEvent = $this->eventService->update($event, $request);
        
        if ($event->isDirty()) {
            return $this->apiResponse(
                new EventResource($updatedEvent),
                'Event updated successfully',
                200
            );
        }
        
        return $this->apiResponse(
            new EventResource($updatedEvent),
            'No changes were made to the event',
            200
        );
    }
    
    /**
     * Remove the specified event from storage.
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Event $event)
    {
        Gate::authorize('delete', $event);
        
        $this->eventService->delete($event);
        
        return $this->apiResponse(
            new EventResource($event),
            'Event deleted successfully',
            200
        );
    }


    /**
     * Set a cover image for the event.
     * @param \App\Http\Requests\Event\SetCoverImageRequest $request
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function setCoverImage(SetCoverImageRequest $request, Event $event)
    {
        Gate::authorize('setCoverImage', $event);
        
        $event = $this->eventService->setCoverImage($event, $request->image_id);
        
        return $this->apiResponse(
            new EventResource($event),
            'Cover image set successfully',
            200
        );
    }

    /**
     * Get upcoming events.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upcoming(Request $request)
    {
        $limit = $request->input('limit', 5);
        $events = $this->eventService->getUpcomingEvents($limit);
        
        return $this->apiResponse(
            EventResource::collection($events),
            'Upcoming events retrieved successfully',
            200
        );
    }

    /**
     * Get events by type.
     * @param \Illuminate\Http\Request $request
     * @param mixed $typeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function byType(Request $request, $typeId)
    {
        $limit = $request->input('limit', 10);
        $events = $this->eventService->getEventsByType($typeId, $limit);
        
        return $this->apiResponse(
            EventResource::collection($events),
            'Events by type retrieved successfully',
            200
        );
    }

    /**
     * Get events by location.
     * @param \Illuminate\Http\Request $request
     * @param mixed $locationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function byLocation(Request $request, $locationId)
    {
        $limit = $request->input('limit', 10);
        $events = $this->eventService->getEventsByLocation($locationId, $limit);
        
        return $this->apiResponse(
            EventResource::collection($events),
            'Events by location retrieved successfully',
            200
        );
    }
}



