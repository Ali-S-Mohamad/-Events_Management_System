<?php

namespace App\Services;

use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /**
     * Get all reservations for the authenticated user.
     */
    public function getUserReservations()
    {
        return Reservation::forCurrentUser()
            ->with([
                'event' => function ($query) {
                    $query->with(['eventType', 'location', 'coverImage']);
                }
            ])
            ->latest()
            ->get();
    }

    /**
     * Get all reservations for a specific event.
     */
    public function getEventReservations(Event $event)
    {
        return $event->reservations()
            ->with('user')
            ->latest()
            ->get();
    }

    /**
     * Create a new reservation.
     */
    public function create(StoreReservationRequest $request): Reservation
    {
        $reservation = DB::transaction(function () use ($request) {
            $reservation = Reservation::create([
                'event_id' => $request->event_id,
                'user_id' => Auth::id(),
                'guests_count' => $request->guests_count,
            ]);

            return $reservation;
        });

        return $reservation->load([
            'event' => function ($query) {
                $query->with(['eventType', 'location']);
            },
            'user'
        ]);
    }

    /**
     * Update an existing reservation.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): Reservation
    {
        // تخزين حالة التغيير قبل التحديث
        $wasChanged = false;

        if ($reservation->guests_count != $request->guests_count) {
            $reservation->guests_count = $request->guests_count;
            $wasChanged = true;
        }

        if ($wasChanged) {
            $reservation->save();
        }

        return $reservation->load([
            'event' => function ($query) {
                $query->with(['eventType', 'location']);
            },
            'user'
        ]);
    }

    /**
     * Cancel (delete) a reservation.
     */
    public function cancel(Reservation $reservation): void
    {
        if (!$reservation->canBeCancelled()) {
            throw new \Exception('لا يمكن إلغاء الحجز لأن الفعالية قد بدأت بالفعل.');
        }

        DB::transaction(function () use ($reservation) {
            $reservation->delete();
        });
    }

    /**
     * Check if user has already reserved for an event.
     */
    public function hasUserReserved(Event $event): bool
    {
        return Reservation::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->exists();
    }

    /**
     * Get the most popular events based on reservation count.
     */
    public function getPopularEvents(int $limit = 5)
    {
        return Event::withCount('reservations')
            ->orderByDesc('reservations_count')
            ->upcoming()
            ->with(['eventType', 'location', 'coverImage'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get events with available spots.
     */
    public function getEventsWithAvailableSpots(int $limit = 10)
    {
        return Event::withCount('reservations')
            ->upcoming()
            ->whereRaw('(SELECT COUNT(*) FROM reservations WHERE reservations.event_id = events.id) < ?', [50]) // افتراض أن الحد الأقصى هو 50
            ->with(['eventType', 'location', 'coverImage'])
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }
}


