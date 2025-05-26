<?php

namespace App\Services;

use App\Models\EventType;

class EventTypeService
{
    public function list()
    {
        return EventType::with('events')->paginate(5);
    }

    public function create($request): EventType
    {
        $EventType = EventType::create($request->validated());
        $EventType->load('events');
        return $EventType;
    }
    public function show(EventType $eventType)
    {
        return $eventType->load('events')->loadCount('events');
    }
    public function update(EventType $eventType, $request): EventType
    {
        $eventType->update($request->validated());
        $eventType->load('events');
        return $eventType;
    }

    public function delete(EventType $eventType): void
    {   
        $eventType->delete();
    }
}
