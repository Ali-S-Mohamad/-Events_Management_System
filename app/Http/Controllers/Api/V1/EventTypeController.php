<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\EventType;

use Illuminate\Http\Request;
use App\Services\EventTypeService;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventTypeResource;
use App\Http\Resources\EventTypeCollection;
use App\Http\Requests\EventType\StoreEventTypeRequest;
use App\Http\Requests\EventType\UpdateEventTypeRequest;

class EventTypeController extends Controller
{
    protected EventTypeService $eventTypeService;
    public function __construct(EventTypeService $eventTypeService)
    {
        $this->eventTypeService = $eventTypeService;
    }

    public static function middleware(): array
    {
        return [
            'auth',
            'role:admin'
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return response()->json($this->eventTypeService->list());
        return $this->apiResponse(new EventTypeCollection($this->eventTypeService->list()), 'All Locations', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventTypeRequest $request)
    {
        $eventType = $this->eventTypeService->create($request);
        if ($eventType) {
            return $this->successResponse(new EventTypeResource($eventType),'Event Type added successfully', 200);
        } else {
            return $this->errorResponse('Not allowed..', 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EventType $eventType)
    {
        $eventType = $this->eventTypeService->show($eventType);
        return $this->apiResponse(new EventTypeResource($eventType), 'The EventType: ', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventTypeRequest $request, EventType $eventType)
    {
        $eventType = $this->eventTypeService->update($eventType, $request);
        if ($eventType){
            return $this->apiResponse(new EventTypeResource($eventType), 'Event Type updated successfully', 200);
        }
        return response()->json($eventType, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventType $eventType)
    {
        $this->eventTypeService->delete($eventType);
        return $this->apiResponse([], 'Event Type deleted successfully', 200);
    }
}
