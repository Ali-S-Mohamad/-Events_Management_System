<?php

namespace App\Services;

use App\Models\EventType;

class EventTypeService
{
    /**
     * Summary of list
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function list()
    {
        return EventType::with('events')->paginate(5);
    }

    /**
     * Summary of create
     * @param mixed $request
     * @return EventType
     */
    public function create($request): EventType
    {
        $EventType = EventType::create($request->validated());
        $EventType->load('events');
        return $EventType;
    }

    /**
     * Summary of show
     * @param \App\Models\EventType $eventType
     * @return EventType
     */
    public function show(EventType $eventType)
    {
        return $eventType->load('events')->loadCount('events');
    }

    /**
     * Summary of update
     * @param \App\Models\EventType $eventType
     * @param mixed $request
     * @return EventType
     */
    public function update(EventType $eventType, $request): EventType
    {
        $eventType->update($request->validated());
        $eventType->load('events');
        return $eventType;
    }

    /**
     * Summary of delete
     * @param \App\Models\EventType $eventType
     * @return void
     */
    public function delete(EventType $eventType): void
    {   
        $eventType->delete();
    }
}
