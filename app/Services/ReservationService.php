<?php

namespace App\Services;

use App\Events\ReservationCreated;
use App\Events\ReservationCancelled;
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
        return Reservation::where('user_id', Auth::id())
            ->with(['event', 'event.eventType', 'event.location'])
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

            // إطلاق حدث إنشاء الحجز
            event(new ReservationCreated($reservation));

            return $reservation;
        });

        return $reservation->load(['event', 'user']);
    }

    /**
     * Update an existing reservation.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): Reservation
    {
        $reservation->update([
            'guests_count' => $request->guests_count,
        ]);

        return $reservation->load(['event', 'user']);
    }

    /**
     * Cancel (delete) a reservation.
     */
    public function cancel(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            // إطلاق حدث إلغاء الحجز
            event(new ReservationCancelled($reservation));
            
            // حذف الحجز
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
}